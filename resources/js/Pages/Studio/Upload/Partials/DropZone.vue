<template>
        <div @dragenter.prevent="toggleActive"
             @dragleave.prevent="toggleActive"
             @dragover.prevent
             @drop.prevent="drop"
             class=" flex justify-center items-center w-full h-full outline-blue-500 transition ease-in-out outline-dashed rounded-lg"
             :class="{'!bg-blue-900/20': active}">
            <div class="flex flex-col items-center justify-center py-8 sm:px-8">
<!--                <PlusSVG class="text-dark-700"/>-->
                <p class="mt-1 ">Drag and drop your files here</p>
                <span class="text-sm mt-2">OR</span>
                <label for="file"
                       class="mt-2 inline-flex items-center px-4 py-2 bg-zinc-800 dark:bg-zinc-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-zinc-800 uppercase tracking-widest hover:bg-zinc-700 dark:hover:bg-white focus:bg-zinc-700 dark:focus:bg-zinc-900 active:bg-zinc-900 dark:active:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150"
                >Select File</label>
                <input
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

// import PlusSVG from "@/Shared/svg/PlusSVG.vue";
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
