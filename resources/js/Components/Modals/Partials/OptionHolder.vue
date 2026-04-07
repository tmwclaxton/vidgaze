<script setup>
    const name = "OptionHolder";
</script>

<template>
    <div
        class="z-10 flex w-max min-w-[12rem] flex-col overflow-hidden rounded-2xl border border-zinc-200/90 bg-white/95 p-1.5 shadow-xl shadow-zinc-950/10 backdrop-blur-md dark:border-zinc-700/80 dark:bg-zinc-900/95 dark:shadow-black/40 sm:p-2"
    >
        <slot />
    </div>
</template>



<style scoped>

</style>
