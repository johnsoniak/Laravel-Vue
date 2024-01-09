<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Str;

class ProductsController extends Controller
{

    /**
     * Get all Products
     */
    public function index(bool $public = true)
    {
        /** Pobierz wszystkie produkty wraz z ich mediami (obrazami) */
        if ($public == true)
            $products = Products::with("images")->whereNotNull("name")->get();
        else
            $products = Products::with("images")->get();

        /** Sprawdź, czy istnieją jakiekolwiek produkty */
        if ($products->isEmpty()) {
            return response()->json(['message' => 'Brak dostępnych produktów.'], 200);
        }

        /** Przygotuj dane do odpowiedzi JSON */




        /** Zwróć listę produktów wraz z ich mediami w odpowiedzi JSON */
        return response()->json(['data' => $products], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function publishImage(Request $request)
    {
        $this->authorize('create', \App\Models\Products::class);

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('file')) {
            // Get the uploaded file from the request
            $file = $request->file('file');
            $image = ImagesController::uploadTemporary($file);
        }

        $products = Products::create([
            'anchor' => null,
            'name' => null,
            'description' => "no-public",
            'currency' => 0,
            'category_product_id' => null,
        ]);

        if (isset($image)) {
            $products->addMedia($image)->toMediaCollection('Food');
            $images = $products->getFirstMedia('Food');
            $url = $products->getFirstMediaUrl('Food');
        }


        if (isset($products->id)) {
            return response()->json([
                'media' => [
                    'id' => $products->id,
                    'file_name' => $images["file_name"],
                    'public_url' => $url,
                ],
            ], 200);
        } else {
            return response()->json(["error" => "Can't upload image"], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Products::class);

        $request->validate([
            'image' => 'nullable|exists:media,id',
            'name' => 'required|string|unique:products',
            'description' => 'required|string',
            'currency' => 'required',
            'category' => 'required|exists:category_products,id',
        ]);

        $imageID = $request->input('image');

        if ($imageID !== null) {
            $publish = MediaController::publicIt($imageID, "\App\Models\Products");
        }

        $anchor = Str::slug($request->input('name'), "-");

        $products = Products::create([
            'anchor' => $anchor,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'currency' => $request->input('currency'),
            'category_product_id' => $request->input('category'),
        ]);

        if ($products) {
            return response()->json(['message' => 'Category created successfully', 'data' => $products], 201);
        } else {
            return response()->json(['message' => 'Category created failed', 'data' => $products], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        /** Pokazywanie jednego wyniku */
        $product = Products::with("images")->find($id);

        if (!$product) {
            return response()->json(['error' => 'Produkt nie został znaleziony.'], 404);
        }

        return response()->json(['data' => $product], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        /** okno edycji */
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $newProducts = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'currency' => 'required',
            'category' => 'required|exists:category_products,id',
        ]);
        $newProducts['anchor'] = Str::slug($request->input('name'), "-");
        $newProducts['category_product_id'] = $request->input('category');

        $products = Products::findOrFail($id);

        $update = $products->update($newProducts);

        if ($update) {
            return response()->json(['message' => 'Product data has saved', 'data' => $products], 201);
        } else {
            return response()->json(['message' => 'Product data can\'t save', 'data' => $products], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products)
    {
        //
    }
}
