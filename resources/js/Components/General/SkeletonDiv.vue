<template>
    <div
        class="h-full w-full"
        :class="shimmer ? 'vg-skeleton-shimmer' : 'bg-zinc-200 dark:bg-zinc-800 animate-pulse'"
    />
</template>

<script>
export default {
    name: "SkeletonDiv",
    props: {
        shimmer: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>

</style>
