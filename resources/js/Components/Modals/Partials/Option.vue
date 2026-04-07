<script setup>
    const name = "Option";
    const props = defineProps({
        success: Boolean,
    });
</script>

<template>
    <div
        class="mt-0.5 flex w-full cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-semibold leading-5 text-zinc-700 transition duration-150 ease-out hover:bg-zinc-100 active:bg-zinc-200/80 dark:text-zinc-200 dark:hover:bg-zinc-800/90 dark:active:bg-zinc-800"
    >
        <slot />
    </div>
</template>



<style scoped>

</style>
