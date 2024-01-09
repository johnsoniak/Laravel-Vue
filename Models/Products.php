<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Images;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Products extends Model implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable;

    use InteractsWithMedia, Searchable, Filterable;

    use Images;

    protected $fillable = ['name', 'description', 'anchor', 'currency', 'category_product_id'];

    protected $appends = [
        'medium_thumb',
        'image_thumb_url',
        'full_name',
    ];

    public static function boot()
    {
        parent::boot();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('Food');
    }



    public function image()
    {
        return $this->getMedia('Food')->first();
    }

    public function images()
    {
        return $this->hasMany(MediaFile::class, 'model_id');
    }

    public function getAllData()
    {
        $data = (object) array(
            "id" => $this->id,
            "name" => $this->name,
            "images" => $this->getImages($this->id),
            "description" => $this->description
        );

        return $data;
    }

    public function getImageUrlAttribute()
    {
        $avatar = $this->image();
        if ($avatar) {
            return $avatar->getFullUrl();
        }

        return null;
    }

    /**
     * Returns the avatar url attribute
     * @return string|null
     */
    public function getImageThumbUrlAttribute()
    {
        $image = $this->image();
        if ($image) {
            return $image->getAvailableFullUrl(['small_thumb']);
        }

        return null;
    }

    public function getMediumThumbAttribute()
    {
        $image = $this->image();
        if ($image) {
            return $image->getAvailableFullUrl(['medium_thumb']);
        }

        return null;
    }

    /**
     * Returns the full_name attribute
     * @return string
     */
    public function getFullNameAttribute()
    {
        $names = [];
        foreach (['first_name', 'middle_name', 'last_name'] as $key) {
            $value = $this->getAttribute($key);
            if ( ! empty($value)) {
                $names[] = $value;
            }
        }

        return implode(' ', $names);
    }

    public function getCollection()
    {
        return $this->getMediaCollection("Food");
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('small_thumb')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
        $this->addMediaConversion('medium_thumb')
            ->fit(Manipulations::FIT_CROP, 600, 600)
            ->nonQueued();
        $this->addMediaConversion('large_thumb')
            ->fit(Manipulations::FIT_CROP, 1200, 1200)
            ->nonQueued();
    }
}
