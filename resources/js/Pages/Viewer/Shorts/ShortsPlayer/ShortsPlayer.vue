
<script setup>
import ThumbsUpIcon from '~/images/icons/like.svg';
import ThumbsDownIcon from '~/images/icons/dislike.svg'
import ShareIcon from '~/images/icons/share.svg'
import CommentsIcon from '~/images/icons/comments.svg'
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {useContentModalStore} from "@/Stores/ContentModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
const contentModalStore = useContentModalStore();
const shareModalStore = useShareModalStore();

const name = 'ShortsPlayer'

let showShare = false;
const comment = false;
const props = defineProps({
    video: {
        type: Object,
        default: null,
    },
});

const share = () => {
    if (showShare) {
        shareModalStore.showMenu = false;
        showShare = false;
        return;
    } else {
        contentModalStore.itemType = 'short';
        contentModalStore.item = props.video;
        contentModalStore.shareContent();
        showShare = true;
    }

};
</script>

<template>
    <section class="w-full h-[calc(100vh-4rem)] overflow-hidden  snap-start flex flex-col text dark:textDark" >

        <div class="relative flex pt-8 py-10  h-full flex flex-col">

            <div class=" mx-auto h-full  flex flex-row align-middle justify-center">

                <div class=" aspect-[9/16] h-full flex-grow flex flex-col">

                    <div class="w-full h-full rounded-xl without-ring flex flex-col relative overflow-hidden">

                        <!--Channel information-->
                        <div class="pb-3 gap-x-2 flex flex-row ">
                            <div class="flex-shrink-0 cursor-pointer w-14 h-14 rounded-full bg-zinc-200 dark:bg-zinc-800">
                                <img class="w-full h-full rounded-full" v-bind:src="video.creator.avatar_url">
                            </div>
                            <div class="  cursor-pointer w-72   rounded-full px-2  ">
                                <div class="flex flex-col pl-2 h-full">
                                    <div class="flex flex-row gap-x-2">
                                        <p class="font-bold text-lg text-left " v-text="video.creator.name"></p>
                                        <SubscribeButton :channel="video.creator" />
                                    </div>
                                    <p class="font-semibold text-xs text-left line-clamp-2 " v-text="video.description"></p>
                                    <p class="font-bold text-xs text-left line-clamp-2 text-red-500 mt-1 hover:shake animate-pulse" v-if="video.live_viewer_count !== '0'" v-text="video.live_viewer_count + ' Watching'"></p>
                                </div>
                            </div>
                        </div>

                        <!--Player-->
                        <div class="bg-zinc-200 dark:bg-zinc-800 w-full   flex-grow rounded-2xl without-ring flex relative overflow-hidden">


                        </div>
                    </div>

                </div>
                <!--Buttons-->
                <div class="flex flex-col gap-4 select-none justify-end text dark:textDark ml-6">
                    <div class="flex flex-col gap-1 cursor-pointer w-14 aspect-square rounded-full">
                        <ThumbsUpIcon class="h-8 mx-auto" />
                        <p class="font-bold text-sm text-center"  v-text="video.likes"/>
                    </div>
                    <div class="flex flex-col gap-1  cursor-pointer w-14 aspect-square rounded-full ">
                        <ThumbsDownIcon class="h-8 mx-auto" />
                        <p class="font-bold text-sm text-center" v-text="video.dislikes"/>
                    </div>
                    <div class="flex flex-col gap-1  cursor-pointer w-14 aspect-square rounded-full">
                        <CommentsIcon class="h-8 mx-auto" />
                        <p class="font-bold text-sm text-center" v-text="video.comment_count"/>
                    </div>

                    <div class="flex flex-col gap-1 cursor-pointer " @click="share">
                        <ShareIcon class="h-8 mx-auto" />
                        <p class="font-bold text-sm text-center">Share</p>
                    </div>
                </div>

            </div>

        </div>

    </section>


</template>
