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
import {onMounted, ref, watch} from "vue";

const contentModalStore = useContentModalStore();
const name = "ContentModal";

function hideMenu() {
    if (contentModalStore.showMenu) {
        contentModalStore.setMenuShow(false);
    }
}

onMounted(() => {
    window.addEventListener('scroll', contentModalStore.setCoordinates);
    window.addEventListener('resize', contentModalStore.setCoordinates);

});



const toggleWatchLater = () => {
    contentModalStore.toggleWatchLater(contentModalStore.itemId, contentModalStore.inWatchLater);
}

const toggleVideoDisinterest = () => {
    contentModalStore.toggleVideoDisinterest(contentModalStore.itemId, contentModalStore.videoDisinterest);
}

const toggleChannelDisinterest = () => {
    contentModalStore.toggleChannelDisinterest(contentModalStore.creatorId, contentModalStore.channelDisinterest);
}

</script>



<template>
    <div id="menu" v-if="contentModalStore.showMenu" v-click-away="hideMenu"
         v-bind:style="{'top': contentModalStore.y + 'px', 'left': contentModalStore.x + 'px'}" class="z-10 absolute w-max h-max">
        <OptionHolder v-if="contentModalStore.itemType === 'video'">
            <div v-if=" $page.props.auth.user !== null" class="flex flex-col ">
                <!--Watch later-->
                <Option v-if="!contentModalStore.inWatchLater " @click="toggleWatchLater">
                    <ClockIcon class="w-5 h-5" />
                    <p>Save to Watch later</p>
                </Option>
                <Option v-if="contentModalStore.inWatchLater"  @click="toggleWatchLater">
                    <TickIcon  class="w-5 h-5"/>
                    <p>Saved to Watch later</p>
                </Option>
                <!--Playlist-->
                <Option  >
                    <PlaylistIcon class="w-5 h-5" />
                    <p>Save to playlist</p>
                </Option>
            </div>

            <!--Share-->
            <Option  >
                <ShareIcon class="w-5 h-5" />
                <p>Share</p>
            </Option>
            <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">
            <div v-if=" $page.props.auth.user !== null" class="flex flex-col ">
                <Option v-if="!contentModalStore.videoDisinterest" @click="toggleVideoDisinterest">
                    <BanIcon class="w-5 h-5" />
                    <p>Not interested</p>
                </Option>
                <Option v-if="contentModalStore.videoDisinterest" @click="toggleVideoDisinterest">
                    <TickIcon class="w-5 h-5" />
                    <p>You won't see this again!</p>
                </Option>
                <Option v-if="!contentModalStore.channelDisinterest" @click="toggleChannelDisinterest" >
                    <CancelIcon class="w-5 h-5" />
                    <p>Don't recommend channel</p>
                </Option>
                <Option v-if="contentModalStore.channelDisinterest" @click="toggleChannelDisinterest">
                    <TickIcon class="w-5 h-5" />
                    <p>Channel hidden</p>
                </Option>
            </div>
            <Option v-if="!contentModalStore.reportVideo" >
                <ReportIcon class="w-5 h-5" />
                <p>Report</p>
            </Option>
        </OptionHolder>

    </div>
</template>

