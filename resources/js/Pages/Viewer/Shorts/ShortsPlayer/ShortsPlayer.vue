
<script setup>
import ThumbsDownIcon from '~/images/icons/dislike.svg'
import ShareIcon from '~/images/icons/share.svg'
import CommentsIcon from '~/images/icons/comments.svg'
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {useContentModalStore} from "@/Stores/ContentModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import LikeDislikeButtons from "@/Components/Buttons/LikeDislikeButtons.vue";
import {computed, onMounted, onUnmounted, ref, watchEffect} from "vue";
import CommentSection from "@/Components/CommentSection/CommentSection.vue";
import {useNavStore} from "@/Stores/NavStore";
const contentModalStore = useContentModalStore();
const shareModalStore = useShareModalStore();
const navStore = useNavStore();

const name = 'ShortsPlayer'

let showShare = false;
const showCommentSection = ref(false);
const props = defineProps({
    video: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        required: true,

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
let observer = ref(null);
const emits = defineEmits(['UpdateFullyVisibleIndex' ]);
onMounted(() => {
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 1.0,
    };


    const handleIntersection = (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                // Emit an event upwards when the player becomes fully visible
                emits('UpdateFullyVisibleIndex',props.index);

            }
        });
    };

    observer = new IntersectionObserver(handleIntersection, options);
    observer.observe(document.getElementById('player_div_holder_' + props.video.external_id));
});

onUnmounted(() => {
    observer.disconnect();
});

const sizeToHide = 1268;

// show comment section shoould be computed property if screen size is mobile
const showCommentSectionMobile = computed(() => {
    if (navStore.width < sizeToHide) {
        return false;
    } else {
        return showCommentSection.value;
    }
});

const hideCommentsButton = computed(() => {
    if (navStore.width < sizeToHide) {
        return false;
    } else {
        return true;
    }
});


</script>

<template>
    <section
        class="w-full h-[calc(100vh-4rem)] overflow-hidden  snap-start flex flex-col text dark:textDark" >
        <div class="relative flex pt-4 py-6  h-full flex flex-col">

            <div class=" mx-auto h-full  flex flex-row align-middle justify-center">

                <div class=" aspect-[9/16] h-full flex-grow flex flex-col">
                <!--<p v-text="video.external_id + ' * ' + video.preferred_source"></p>-->

                    <div class="w-full h-full  without-ring flex flex-col relative overflow-hidden">

                        <!--Channel information-->
                        <div class="pb-3 gap-x-2 flex flex-row h-24">
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
                                    <p class="font-bold text-xs text-left line-clamp-2 text-red-500 dark:text-red-500 mt-1 hover:shake " v-if="video.live_viewer_count !== '0'" v-text="video.live_viewer_count + ' Watching'"></p>
                                </div>
                            </div>
                        </div>

                        <!--Player-->
                        <div  :id="'player_div_holder_' + video.external_id" class=" bg-black w-full   flex-grow without-ring flex relative overflow-hidden"
                        :class="showCommentSectionMobile ? 'rounded-l-2xl' : 'rounded-2xl'">
                            <!--<div :id="video.external_id" class="w-full h-full">-->
                            <!--</div>-->
                        </div>
                    </div>

                </div>

                <div v-if="showCommentSectionMobile"  class="w-96 mt-24 overflow-y-auto border border-zinc-300 dark:border-zinc-500 p-2 py-4  flex-grow rounded-r-2xl without-ring flex relative overflow-hidden">
                    <CommentSection :item="video" :simple="true" />
                </div>


                <!--Buttons-->
                <div class="flex flex-col gap-4 select-none justify-end text dark:textDark ml-6">

                    <LikeDislikeButtons :video="video" :orientation-vertical="true"/>

                    <div v-if="hideCommentsButton" class="flex flex-col gap-1  cursor-pointer " @click="showCommentSection = !showCommentSection">
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
