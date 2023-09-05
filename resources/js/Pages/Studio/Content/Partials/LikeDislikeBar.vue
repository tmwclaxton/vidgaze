<template>
    <div class="flex justify-between">
        <p>{{likes}}</p>
        <p>{{dislikes}}</p>
    </div>
    <div class="rounded-full overflow-hidden">
        <div v-if="hasRatio" class="flex h-2">
            <div class="bg-green-500 h-full" :style="{ width: likeRatio + '%' }"/>
            <div class="bg-red-500 h-full" :style="{ width: dislikeRatio + '%' }"/>
        </div>
        <div v-else class="flex h-2">
            <div class="bg-gray-300 dark:bg-gray-400 h-full w-full"/>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';

const props = defineProps({
    likes: {
        type: Number,
        required: true
    },
    dislikes: {
        type: Number,
        required: true
    }
});

const total = props.likes + props.dislikes;

const hasRatio = computed(() => {
    return total > 0;
});

const likeRatio = computed(() => {
    return total === 0 ? '50' : ((props.likes / total)) * 100;
});

const dislikeRatio = computed(() => {
    return 100 - (total === 0 ? '50' : ((props.likes / total)) * 100);
});
</script>

