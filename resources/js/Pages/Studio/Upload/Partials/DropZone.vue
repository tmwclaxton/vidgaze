<template>
        <div @dragenter.prevent="toggleActive"
             @dragleave.prevent="toggleActive"
             @dragover.prevent
             @drop.prevent="drop"
             @drop="selectedFile"
             @click="$refs.file.click()"
             class=" flex justify-center items-center w-full h-full outline-zinc-500 outline-dashed rounded-lg cursor-pointer"
             :class="{'!bg-blue-900/20': active}">
            <div class="flex flex-col items-center justify-center py-8 sm:px-8">
                <div class="flex flex-row gap-x-2">
                    <font-awesome-icon :icon="['fas', 'plus']" class=" my-auto"/>
                    <p class="mt-1 ">Drag and drop your files here</p>
                </div>
                <span class="text-sm mt-2">OR</span>
                <label for="file"
                       class="mt-2 inline-flex items-center px-4 py-2 bg-zinc-800 dark:bg-zinc-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-zinc-800 uppercase tracking-widest hover:bg-zinc-700 dark:hover:bg-white focus:bg-zinc-700 dark:focus:bg-zinc-900 active:bg-zinc-900 dark:active:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150"
                >Select File</label>
                <input
                    ref="file"
                    @input="selectedFile"
                    type="file"
                    name="files[]"
                    id="file"
                    class="hidden"
                    multiple
                >
            </div>

        </div>

</template>
<script setup>
import {ref} from "vue";

let active = ref(false);

const toggleActive = () => {
    active.value = !active.value;
}

const drop = (event) =>{
    active.value = !active.value;
}

const selectedFile = () =>{
}
</script>
