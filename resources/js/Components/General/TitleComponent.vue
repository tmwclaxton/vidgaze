<template>
    <div class="flex flex-row gap-2">
        <slot  class="w-6 h-6 my-auto"></slot>
        <p
            class="bg-gradient-to-r from-cyan-600 via-violet-600 to-fuchsia-600 bg-clip-text text-2xl font-bold text-transparent dark:from-cyan-400 dark:via-violet-400 dark:to-fuchsia-400"
        >
            {{ text }}
        </p>
    </div>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            required: true
        }
    }
};
</script>
