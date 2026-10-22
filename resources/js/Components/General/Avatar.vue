<script setup>
import {computed, ref, watch} from "vue";

// Round avatar that falls back to the first letter of the name when there is
// no image or it fails to load (Rumble creators often have no avatar).
const props = defineProps({
    src: {type: String, default: null},
    name: {type: String, default: ''},
});

const failed = ref(false);
watch(() => props.src, () => { failed.value = false; });

const initial = computed(() => (props.name || '?').trim().charAt(0).toUpperCase() || '?');
</script>

<template>
    <img v-if="src && !failed" v-bind:src="src" :alt="name ? `${name} avatar` : 'Channel avatar'"
         class="rounded-full object-cover" @error="failed = true"/>
    <span v-else role="img" :aria-label="name ? `${name} avatar` : 'Channel avatar'"
          class="rounded-full bg-zinc-300 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-100 font-bold flex items-center justify-center select-none">
        {{ initial }}
    </span>
</template>
