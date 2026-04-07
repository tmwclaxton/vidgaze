<script setup>
const name = 'TopNavButton';
</script>

<template>
    <div class="flex flex-row items-center justify-center text-sm capitalize cursor-pointer
    bg-zinc-900 hover:bg-zinc-800 text-white p-2 px-5 rounded-xl font-bold flex flex-row space-x-3
    ring-1 ring-white/10 transition-all duration-200 hover:ring-cyan-400/40 hover:shadow-[0_0_24px_-8px_rgba(34,211,238,0.32)]">
        <slot></slot>
    </div>
</template>

