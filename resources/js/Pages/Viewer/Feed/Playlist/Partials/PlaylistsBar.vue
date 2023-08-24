<script setup>
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import Title from "@/Components/General/Title.vue";
import HistoryIcon from '~/images/icons/subscriptions.svg';
import {onMounted, ref, watch} from "vue";
import PlaylistCard from "@/Components/Cards/PlaylistCard/PlaylistCard.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import {useAuthStore} from "@/Stores/AuthStore";

const name = "PlaylistBar";

const props = defineProps({
    text: {
        type: String,
        required: true
    },
});

const playlists = ref([]);
//
onMounted(async () => {
    {
        if (useAuthStore().user !== null) {
            setTimeout(async () => {
                playlists.value = await usePlaylistModalStore().getPlaylists();
            }, 1000);
        } else {
            watch(() => useAuthStore().user, async () => {
                playlists.value = await usePlaylistModalStore().getPlaylists();
            });
        }
    }
});
</script>
<template>
    <div class="flex flex-row w-full justify-between ">
        <Title :text="props.text" class="my-auto">
            <HistoryIcon class="w-5"/>
        </Title>
        <QuaternaryButton class="h-max" @click="">
            <span class="font-semibold">Create new</span>
        </QuaternaryButton>
    </div>

    <row-divider class="mt-6 mb-3 rounded-2xl"></row-divider>

    <div class="mx-1 mb-14 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4 overflow-x-scroll gap-4">
        <template v-if="playlists !== undefined && playlists.length > 0" v-for="playlist in playlists" >
            <playlist-card :item="playlist" channel="true"/>
        </template>

        <template v-else >
            <VideoStreamSkeleton v-for="i in 6"/>
        </template>
    </div>
</template>

