<script setup>

const name = 'NotificationCard';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

</script>

<template>
    <Link :href="route('watch.show', {slug: item.slug})">
        <div class="py-1 pb-2 rounded cursor-pointer hover:bg-zinc-200 dark:hover:bg-zinc-700 overflow-hidden
            grid grid-cols-6 w-full h-18">
            <span class=" col-span-1">
                <img class="w-full p-2  aspect-square rounded-full" v-bind:src="item.creator.avatar_url"
                     alt="">
            </span>
            <span class=" col-span-3 items-start grid grid-cols-1">
                <span class="line-clamp-2 w-full text-xs font-bold text-left pt-2 px-1" v-text="item.creator.name + ' · ' + item.title"/>
                <span class="text-zinc-500 line-clamp-1 w-full text-xs font-bold text-left pt-1 px-1"
                      v-text="item.type ? item.time_published : item.viewers"/>
            </span>
            <span class=" col-span-2 pt-2 px-4">
                <img alt="" class=" h-12 w-full rounded-md" v-bind:src="item.thumbnail_url">
            </span>
        </div>
    </Link>
</template>
