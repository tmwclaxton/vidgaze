<script setup>
    const name = "Option";
    const props = defineProps({
        success: Boolean,
    });
</script>

<template>
    <div class="flex flex-row space-x-2 block w-full px-4 py-2 mt-0.5
    text-left text-sm leading-5 text-zinc-700 dark:text-zinc-300 font-bold cursor-pointer
    hover:bg-zinc-200 dark:hover:bg-zinc-800 transition duration-150 ease-in-out rounded-md">
        <slot />
    </div>
</template>



<style scoped>

</style>
