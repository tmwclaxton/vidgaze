
<script setup>
import LikeDislikeBar from "@/Pages/Studio/Content/Partials/LikeDislikeBar.vue";

const name = 'TableItem';
import { defineProps, computed } from 'vue';


const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    selected: {
        type: Boolean,
        required: true
    },
    link: {
        type: Function,
        required: true
    },
    thumbnail: {
        type: Function,
        required: true
    }
});

const emits = defineEmits(['toggleSelect']);

</script>
<template>
    <tr class="border-b-2 hover:bg-zinc-200 dark:hover:bg-zinc-900 dark:border-zinc-800">
        <td class="w-4 p-4 cursor-pointer">
            <div class="flex items-center" @click="$emit('toggleSelect', item.id)">
                <!--if item id is in selected-->
                <input v-bind:checked="selected"
                       type="checkbox"
                       class="rounded w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600 hover:dark:bg-zinc-700 focus:dark:bg-zinc-700">

            </div>
        </td>
        <td class="overflow-hidden ">
            <Link :href="link" class="w-full">
                <div class="mx-2 my-2 flex flex-row items-center justify-center space-x-3 max-h-[90px] max-w-[300px]">
                    <div class="w-[160px] h-[90px] rounded-lg bg-zinc-300 dark:bg-zinc-900 flex-shrink-0">
                        <img v-if="thumbnail"
                             :src="thumbnail"
                             class="w-[160px] h-[90px] rounded-lg">
                    </div>
                    <div class="w-full overflow-y-scroll  max-h-[90px]" >
                        <h3 class="font-semibold text-lg hover:underline break-words text-center ">{{ item.title }}</h3>
                    </div>
                </div>
            </Link>
        </td>
        <td>
            <p v-text="item.type" class="capitalize"/>
        </td>
        <td>
            <p v-text="item.visibility"
               class="capitalize"/>
        </td>
        <td>
            <div v-if="item.time_published">
                <p>{{ item.time_published }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Published</p>
            </div>
            <div v-if="!item.time_published && item.time_uploaded">
                <p>{{ item.time_uploaded }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Uploaded</p>
            </div>
            <div v-if="item.publish_time">
                <p>{{ item.publish_time_human_readable }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Publish Time</p>
            </div>
        </td>
        <td>
            <p v-text="item.view_count ? item.view_count : 'N/A'"/>
        </td>
        <td>
            <p v-text="item.live_viewer_count ? item.live_viewer_count : 'N/A'"/>
        </td>
        <td>
            <p v-text="item.comment_count ? item.comment_count : 'N/A'"/>
        </td>
        <td>
            <LikeDislikeBar v-if="item.like_count >= 0" :dislikes="item.dislike_count" :likes="item.like_count"/>
            <p v-else>N/A</p>
        </td>
    </tr>
</template>

