<template>
    <div class="w-full border-1 border-gray-300 border-box">
        <div
            v-for="(tag, index) in modelValue"
            :key="tag"
            class="h-[30px] float-left mr-[10px] bg-gray-300 rounded-[5px] px-[5px] leading-[30px] mb-[10px] dark:bg-gray-900 dark:text-gray-300"
        >
            <span class="cursor-pointer opacity-75" @click="removeTag(index)">x</span>
            {{ tag }}
        </div>
        <input
            type="text"
            placeholder="Add a tag"
            class="border-1 focus:outline-0 outline-red-500 outline-2 leading-[50px] bg-transparent h-[30px] focus:ring-2 border-zinc-300 dark:border-zinc-600 focus:!border-blue-600 rounded-lg"
            @keydown="addTag"
            @keydown.delete="removeLastTag"
        />
    </div>
</template>

<script setup>
import {ref, defineProps, defineEmits, onMounted} from 'vue';

const { modelValue } = defineProps(['modelValue']);
const emit = defineEmits();

const tags = ref(modelValue || []);

function addTag(e) {
    if (e.code === 'Enter' || e.code === 'Comma' || e.code === 'Tab') {
        e.preventDefault();
        var val = e.target.value.trim();

        if (val.length > 0 && tags.value.indexOf(val) === -1) {
            tags.value.push(val);
            emit('update:modelValue', tags.value); // Emit the updated tags array to the parent
            e.target.value = '';
        }
    }
}

function removeTag(index) {
    tags.value.splice(index, 1);
    emit('update:modelValue', tags.value); // Emit the updated tags array to the parent
}

function removeLastTag(e) {
    if (e.target.value.length === 0 && tags.value.length > 0) {
        removeTag(tags.value.length - 1);
    }
}
</script>
