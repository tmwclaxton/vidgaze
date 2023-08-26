 <script setup>
import TickIcon from '#icons/tick.svg';
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/playlists.svg';
import BanIcon from '#icons/ban.svg';
import CancelIcon from '#icons/cancel.svg';
import ReportIcon from '#icons/report.svg';
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { useContentModalStore } from "@/Stores/ContentModalStore";
import {onMounted, onUnmounted, ref, watch} from "vue";
import { usePlaylistModalStore } from "@/Stores/PlaylistModalStore";
import { useShareModalStore } from "@/Stores/ShareModelStore";
import {useAuthStore} from "@/Stores/AuthStore";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import {router} from "@inertiajs/vue3";

const playlistModalStore = usePlaylistModalStore();
const name = "ContentModal";

const emits = defineEmits(['close']);

const props = defineProps({
    playlist: {
        type: Object,
        required: true
    }
});

onMounted(() => {

});

const confirmDeletePlaylist = () => {
    // confirm that the user wants to close the mini player as it will destroy the queue
    useConfirmModalStore().buttonOneText = 'Cancel';
    useConfirmModalStore().buttonTwoText = 'Delete';
    useConfirmModalStore().title = 'Are you sure, this will delete your playlist?';
    useConfirmModalStore().show = true;
    useConfirmModalStore().continue = () => {
        usePlaylistModalStore().deletePlaylist(props.playlist.id);
        // redirect to library page
        router.visit(route('feed.library'));

    }
}


</script>



<template>
    <div class="z-10 absolute top-5 w-max h-max">
        <OptionHolder >
            <div v-if=" useAuthStore().user !== null" class="flex flex-col ">
                <!--Watch later-->
                <Option @click="confirmDeletePlaylist">
                    <font-awesome-icon :icon="['fas', 'trash']"  class="w-5 h-5" />
                    <p>Delete Playlist</p>
                </Option>
            </div>
        </OptionHolder>

    </div>
</template>

