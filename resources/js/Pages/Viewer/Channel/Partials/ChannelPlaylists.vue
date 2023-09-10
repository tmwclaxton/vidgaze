<script setup>
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import PlaylistCard from "@/Components/Cards/PlaylistCard/PlaylistCard.vue";
import {onMounted, ref, watch} from "vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useAuthStore} from "@/Stores/AuthStore";

const name = 'ChannelPlaylists';

const props = defineProps({
    channel: {
        type: Object,
        required: true
    },
});
const playlists = ref([]);
const loading = ref(true);
onMounted(async () => {
    {
        usePlaylistModalStore().videoIds = [];
        if (useAuthStore().user !== null) {
            await usePlaylistModalStore().getPlaylists(props.channel.id).then((response) => {
                playlists.value = response.playlists;
                loading.value = false;
            });
        } else {
            watch(() => useAuthStore().user, async () => {
                await usePlaylistModalStore().getPlaylists(props.channel.id).then((response) => {
                    playlists.value = response.playlists;
                    loading.value = false;
                });
            });
        }
    }
});
</script>
<template>

    <div class="mx-1 mb-14 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4 overflow-x-scroll gap-4">
        <template v-if="playlists.length > 0" v-for="playlist in playlists" >
            <playlist-card :item="playlist" :channel="true"/>
        </template>

        <!--<template v-else  >-->
        <!--    <VideoStreamSkeleton v-if="loading" v-for="i in 6"/>-->
        <!--</template>-->
    </div>
    <div v-if="playlists.length === 0 && !loading" class="flex flex-col items-center justify-center">
        <h1 class="text-2xl font-semibold">No playlists found</h1>
    </div>

</template>
