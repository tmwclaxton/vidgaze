<template>
    <VideoStreamSkeleton
        v-for="i in blocks * 12"
        :key="`${prefix}-${i}`"
    />
    <template
        v-for="j in blocks * 2"
        :key="`${prefix}-2xl-${j}`"
    >
        <div class="hidden 2xl:contents">
            <VideoStreamSkeleton />
        </div>
    </template>
</template>

<script setup>
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";

defineProps({
    /**
     * Each block is 12 cells below 2xl (full rows for 2/3/4/6 cols) plus 2 cells only at 2xl (7 cols).
     */
    blocks: {
        type: Number,
        default: 1,
        validator: (n) => Number.isInteger(n) && n >= 1 && n <= 6,
    },
    prefix: {
        type: String,
        default: "vg-vgrid-sk",
    },
});
</script>
