<script setup>

import {onBeforeMount, ref} from "vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import LikeDislikeBar from "@/Pages/Studio/Content/Partials/LikeDislikeBar.vue";
import TableItem from "@/Pages/Studio/Content/Partials/TableItem.vue";

const name = 'StudioContent';
const selected = ref([]);
const items = ref([]);

onBeforeMount(async () => {
    await axios.get(route("api.studio.content")).then((response) => {
        items.value = response.data.videos.data.concat(response.data.videoDrafts.data);
    });
});

const toggleSelectAll = () => {
    // if not all selected, select all
    if (selected.value.length !== items.value.length) {
        selected.value = [];
        items.value.forEach((item) => {
            selected.value.push(item.id);
        });
    } else {
        selected.value = [];
    }
};

const toggleSelect = (id) => {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter((item) => item !== id);
    } else {
        selected.value.push(id);
    }
};

const findLink = (item) => {
    if (item.type === 'video') {
        return route('studio.video.edit', item.slug);
    }
    if (item.type === 'video draft') {
        return route('studio.video.draft.edit', item.slug);
    }
    return '';
};

const thumbnail = (item) => {
    if (item.type === "video") {
        return item.thumbnail_url;
    }
    if (item.type === "video draft") {
        return item.thumbnail_path;
    }
    return '';
};
</script>

<template>
    <SeoHead
        title="Studio content"
        description="Manage published videos and drafts across connected platforms in VidGaze Studio."
        noindex
    />

    <ConsistentPadding >
        <TitleComponent :text="'VidGaze Cross-Platform Content Manager'">
            <font-awesome-icon :icon="['fas', 'clapperboard']" class="w-6 h-6 my-auto"/>
        </TitleComponent>
        <p>Edit your YouTube, TikTok, Dailymotion and Vimeo content all in one place all at the same time!</p>

<!--        table-->
<!--        <div class="relative overflow-x-scroll">-->

            <table class="w-full text-sm text-left text-center  mt-10">
                <thead
                    class="text-xs text-zinc-700 uppercase border-y-2 dark:border-zinc-800 dark:text-zinc-200">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center" @click="toggleSelectAll">
                            <input type="checkbox" v-bind:checked="selected.length === items.length"
                               class="rounded w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600 hover:dark:bg-zinc-700 focus:dark:bg-zinc-700">
                        </div>
                    </th>
                    <th scope="col" class="w-[300px]">
                    </th>
                    <th scope="col" class="py-3 ">
                        Type
                    </th>
                    <th scope="col" class="py-3 ">
                        Visibility
                    </th>
                    <th scope="col" class="py-3 cursor-pointer">
                    <span class="flex flex-row w-full justify-center">
                        <p>Date</p>
                    </span>
                    </th>
                    <th scope="col" class="py-3 ">
                        Views
                    </th>
                    <th scope="col" class="py-3 ">
                        Live Viewers
                    </th>
                    <th scope="col" class="py-3  ">
                        Comments
                    </th>
                    <th scope="col" class="py-3">
                        Likes (vs. dislikes)
                    </th>
                    <!--<th scope="col" class="px-6 py-3 cursor-pointer" @click="toggleDirection">-->
                    <!--    <span class="flex flex-row">-->
                    <!--        Live viewers-->
                    <!--        &lt;!&ndash;<x-icon name="extend-up"&ndash;&gt;-->
                    <!--        &lt;!&ndash;        class="{{ sortAsc ? 'rotate-180' : ''  }}  fill w-2 ml-2"/>&ndash;&gt;-->
                    <!--    </span>-->
                    <!--</th>-->
                </tr>
                </thead>
                <tbody class="h-full" :key="selected">
                    <TableItem v-for="item in items" :key="item.id" :item="item" :link="findLink(item)" :thumbnail="thumbnail(item)"
                               @toggleSelect="toggleSelect($event)"
                               :selected="selected.includes(item.id)"
                    />
                </tbody>
            </table>
<!--        </div>-->

    </ConsistentPadding>
</template>

