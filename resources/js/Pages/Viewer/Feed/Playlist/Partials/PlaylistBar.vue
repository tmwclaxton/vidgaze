<script setup>
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import Title from "@/Components/General/Title.vue";
import HistoryIcon from '~/images/icons/subscriptions.svg';
import {onMounted, ref} from "vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";

const name = "PlaylistBar";

const props = defineProps({
    route: {
        type: String,
        required: true
    },
    text: {
        type: String,
        required: true
    },
    id: {
        type: String,
        required: true
    }
});

const videos = ref([]);

onMounted(async () => {
    {
        setTimeout(async () => {

            let playlist;
            [playlist, videos.value] = await usePlaylistModalStore().getPlaylist(props.id,0,6);

        }, 1000);
    }
});

</script>
<template>
    <div class="flex flex-row w-full justify-between ">
        <Link v-bind:href="props.route">
            <Title :text="props.text">
                <HistoryIcon class="my-auto w-5"/>
            </Title>
        </Link>
        <Link v-bind:href="props.route" class="my-auto">
            <QuaternaryButton class="h-max" @click="">
                <span class="font-semibold">See all</span>
            </QuaternaryButton>
        </Link>
    </div>

    <row-divider class="mt-6 mb-3 rounded-2xl"></row-divider>

    <div class="mx-1  mb-5 flex grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 ml:grid-cols-4 4xl:grid-cols-6 gap-4">
        <template v-if="videos !== [] && videos.length > 0" v-for="video in videos">
            <VideoStreamCard :item="video"/>
        </template>
        <template v-else>
            <VideoStreamSkeleton v-for="i in 6"/>
        </template>

    </div>

</template>

