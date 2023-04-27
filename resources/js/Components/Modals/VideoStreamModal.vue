<script setup>
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/playlists.svg';
import BanIcon from '#icons/ban.svg';
import CancelIcon from '#icons/cancel.svg';
import ReportIcon from '#icons/report.svg';
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { useContentModalStore } from "@/Stores/ContentModalStore";
import {onMounted, ref, watch} from "vue";

const contentModalStore = useContentModalStore();
const name = "VideoStreamModal";

function hideMenu() {
    if (contentModalStore.getMenuShow()) {
        contentModalStore.setMenuShow(false);
    }
}

onMounted(() => {
    window.addEventListener('scroll', contentModalStore.setCoordinates);
    window.addEventListener('resize', contentModalStore.setCoordinates);

});

// watch(() => contentModalStore.getMenuShow(), (newVal) => {
//     const menuRef = document.getElementById("menu");
//     if (menuRef !== null) {
//         this.setMenuSize(menuRef.offsetWidth, menuRef.offsetHeight);
//     }
// });
</script>



<template>
    <div id="menu" v-if="contentModalStore.getMenuShow()" v-click-away="hideMenu"
         v-bind:style="{'top': contentModalStore.getY() + 'px', 'left': contentModalStore.getX() + 'px'}" class="z-10 absolute w-max h-max">
        <OptionHolder>
            <Option  >
                <ClockIcon class="w-5 h-5" />
                <p>Save to Watch later</p>
            </Option>
            <Option  >
                <PlaylistIcon class="w-5 h-5" />
                <p>Save to playlist</p>
            </Option>
            <Option  >
                <ShareIcon class="w-5 h-5" />
                <p>Share</p>
            </Option>
            <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">
            <Option  >
                <BanIcon class="w-5 h-5" />
                <p>Not interested</p>
            </Option>
            <Option  >
                <CancelIcon class="w-5 h-5" />
                <p>Don't recommend channel</p>
            </Option>
            <Option  >
                <ReportIcon class="w-5 h-5" />
                <p>Report</p>
            </Option>
        </OptionHolder>

    </div>
</template>

