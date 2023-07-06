<script setup>
const name = 'TopNavButton';
</script>

<template>
    <div class="flex flex-row items-center justify-center text-sm capitalize cursor-pointer select-none
    bg-zinc-900 hover:bg-zinc-800 text-white p-2 px-5 rounded-md font-bold flex flex-row space-x-3">
        <slot></slot>
    </div>
</template>

