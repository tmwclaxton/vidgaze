<script setup>

import {onBeforeMount, ref} from "vue";
import {Head} from "@inertiajs/vue3";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import LikeDislikeBar from "@/Pages/Studio/Content/Partials/LikeDislikeBar.vue";


onBeforeMount(async () => {
    await axios.get(route("api.studio.content")).then((response) => {
        videos.value = response.data.videos.data;
        video_drafts.value = response.data.videoDrafts.data
    });
});

const video_drafts = ref([]);
const videos = ref([]);

</script>

<template>
    <Head title="Studio Content" />

    <ConsistentPadding >
        <TitleComponent :text="'Channel Content'">
            <font-awesome-icon :icon="['fas', 'clapperboard']" class="w-6 h-6 my-auto"/>
        </TitleComponent>

<!--        table-->
<!--        <div class="relative overflow-x-scroll">-->

            <table class="w-full text-sm text-left text-center">
                <thead
                    class="text-xs text-zinc-700 uppercase border-y-2 dark:border-zinc-800 dark:text-zinc-200">
                <tr>
                    <th scope="col" class="w-[300px]">
<!--                        <div class="flex items-center" wire:click="toggleSelectAll()">-->
<!--                            <input type="checkbox" {{ selectAll ? 'checked' : ''  }}-->
<!--                            class="w-4 h-4 text-blue-600  border-zinc-300 rounded focus:ring-offset-0 focus:outline-none    focus:ring-0  ring-transparent dark:bg-zinc-700 dark:border-zinc-600">-->
<!--                        </div>-->
                    </th>

                    <th scope="col" class="py-3 ">
                        Visibility
                    </th>
                    <th scope="col" class="py-3 cursor-pointer">
<!--                    <span class="flex flex-row">-->
                        Date
<!--                    </span>-->
                    </th>
                    <th scope="col" class="py-3 ">
                        Views
                    </th>
                    <th scope="col" class="py-3  ">
                        Comments
                    </th>
                    <th scope="col" class="py-3">
                        Likes vs Dislikes
                    </th>
                </tr>
                </thead>
                <tbody class="h-full">
                    <tr v-for="videoDraft in video_drafts"
                        class="border-b-2 hover:bg-zinc-200 dark:hover:bg-zinc-900 dark:border-zinc-800">
                        <td>
                            <div class="ml-2 my-2 flex space-x-3 max-h-[90px] max-w-[300px]">
                                <Link :href="route('studio.video.draft.edit', videoDraft.slug)">
                                    <img src="https://picsum.photos/1600/900" class="w-[160px] h-[90px] rounded-lg">
                                </Link>
                                <div class="max-w-[120px]">
                                    <Link :href="route('studio.video.draft.edit', videoDraft.slug)">
                                        <h3 class="font-semibold text-lg hover:underline">{{ videoDraft.title }}</h3>
                                    </Link>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ videoDraft.visibility }}
                        </td>
                        <td>
                            {{ videoDraft.created_at }}
                        </td>
                        <td>
                            N/A
                        </td>
                        <td>
                            N/A
                        </td>
                        <td>
                            N/A
                        </td>
                    </tr>
                    <tr v-for="video in videos"
                        class="border-b-2 hover:bg-zinc-200 dark:hover:bg-zinc-900 dark:border-zinc-800">
                        <td>
                            <div class="ml-2 my-2 flex space-x-3 max-h-[90px] max-w-[300px]">
                                <a href="route('studio.video.edit', video.slug)">
                                    <img :src="video.thumbnail_url" class="w-[160px] h-[90px] rounded-lg">
                                </a>
                                <div class="max-w-[120px]">
                                    <a href="route('studio.video.edit', video.slug)">
                                        <h3 class="font-semibold text-lg hover:underline">{{ video.title }}</h3>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ video.visibility }}
                        </td>
                        <td>
                            <div v-if="video.time_published">
                                <p>{{ video.time_published }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Published</p>
                            </div>
                            <div v-else="video.time_uploaded">
                                <p>{{ video.time_uploaded }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Uploaded</p>
                            </div>
                        </td>
                        <td>
                            {{ video.view_count }}
                        </td>
                        <td>
                            {{ video.comment_count }}
                        </td>
                        <td>
<!--                            {{ video.like_count }} ({{ video.dislike_count }})-->
<!--                            make a bar of green and red for like to dislike ratio-->
                            <LikeDislikeBar :dislikes="video.dislike_count" :likes="video.like_count"/>


                        </td>
                    </tr>
                </tbody>
            </table>
<!--        </div>-->

    </ConsistentPadding>
</template>

