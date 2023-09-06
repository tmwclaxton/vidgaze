<script setup>
const name = 'StudioLink';
const props = defineProps({
    link: {
        type: String,
        required: true
    },
    text: {
        type: String,
        required: true
    }
});
</script>
<template>
    <Link :href="link" :text="text"
          class="w-max bg-blue-600 hover:bg-blue-700 dark:bg-blue-400 dark:hover:bg-blue-300 dark:text-vidgaze-blue px-2 py-2 ml-auto text-sm font-bold uppercase rounded-sm cursor-pointer h-max text-white "/>
</template>
