<template>
    <div>
        <input
            class="form-control form-control-lg"
            type="text"
            v-model="setIs"
            @keyup="keyUp"
            @focus="handleFocus"
            @blur="handleBlur"
        />

        <div class="form-control row" style="display: contents" v-if="isActive">
            <button
                v-for="value in dataVariables"
                class="form-control col-12"
                @click.prevent="handleClick(value.id, value.name)"
                style="width: 100%;border-top: #dfe0e1 1px solid"
                :value="value.id"
                :key="value.id"
            >
                {{ value.name }}
            </button>
        </div>
    </div>
</template>

<script setup>
import {ref, defineProps, onMounted, defineEmits} from 'vue';
import axios from "axios";

const props = defineProps(['selectValues', 'url']);
let setIs = ref('');
const isActive = ref(false)
const url = props.url;

let dataVariables = ref({})
const emit = defineEmits();

const getData = async (url) => {
    const response = await axios.get(url)

    dataVariables = response.data.data.map(variable => ({
      id: variable.id,
      name: variable.name
    }));
}

getData(url)

const keyUp = () => {
    const link = props.url+"?search="+setIs.value;
    getData(link)
}

const handleFocus = () => {
    isActive.value = true
}

const handleBlur = () => {
    setTimeout(() => {
        isActive.value = false
    }, 100)
}

const handleClick = (id, name) => {
    setIs.value = name
    emit('categoryId', id);
}


</script>
