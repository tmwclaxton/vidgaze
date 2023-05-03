<script setup>
//props below
defineProps({
    item: Object,
    channel_page: Boolean,
});
const name = 'Duration';
</script>
<template>
    <div class="py-auto px-2 flex flex-col align-middle text-white font-semibold text-sm bg-black opacity-75
                    absolute bottom-0 right-0 rounded dark:text-zinc-200">

        <p class="my-auto" v-text=" item['duration']"> </p>
    </div>
</template>


