<script setup>
import {usePlayerStore} from "@/Stores/PlayerStore";
import {computed, onMounted, ref} from "vue";
import SuggestionsScreen from "@/Pages/Viewer/Watch/Partials/SuggestionsScreen/SuggestionsScreen.vue";
import {useQueueStore} from "@/Stores/QueueStore";
import UpNextVideo from "@/Pages/Viewer/Watch/Partials/UpNextVideo.vue";
const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const name = "EndScreen";
const readyForNextVideo = ref(false);
const timer = ref(8);

const props = defineProps({
    item: Object,
})


const nextUpScreen = computed(() => {
    return (queueStore.items.length > queueStore.index + 1);
});

const playNext = () => {
    // playerStore.playNext();
    // nextUpScreen.value = true;
    // readyForNextVideo.value = false;
}

const resetPlayer = () => {
    playerStore.buildPlayer('watch_player', props.item, 0, true);
    // nextUpScreen.value = true;
    // readyForNextVideo.value = false;
}



// mode if no players, check if queue has a next video if so then show play next screen else show suggestions screen



</script>

<template>
    <div v-show="playerStore.endScreen" id="endScreen"
         class="w-full h-full  without-ring flex relative duration-600 transition ">
        <div v-if="nextUpScreen"
            id="nextUpScreen"
             class=" mx-auto mt-5 sm:my-auto w-96  flex flex-col gap-y-3">
            <p class="text-left text text-zinc-400 font-bold ">Up next in <span
                class="text-white" id="myTimer"></span></p>
            <div class="  ">

                <!--nex video-->
                <UpNextVideo :item="queueStore.items[queueStore.index + 1]"/>

            </div>
            <div id="buttons" class="  flex flex-row gap-x-4 select-none ">

                <div @click="nextUpScreen = false;readyForNextVideo = false;"
                     class="bg-zinc-900 rounded-full p-2 px-14 w-max cursor-pointer image-wrapper shine">
                    <p class="   uppercase text-white text-sm font-bold">
                        Cancel
                    </p>
                </div>
                <div @click="playNext()"
                     class="bg-zinc-800 rounded-full p-2 px-14 w-max cursor-pointer  image-wrapper shine">
                    <p class=" uppercase text-white text-sm font-bold">
                        Play now
                    </p>
                </div>
            </div>
        </div>
        <!--suggestion screen-->
        <SuggestionsScreen v-else :item="props.item"/>

        <div class="absolute top-5 left-5 hidden sm:flex flex-row select-none cursor-pointer">
            <a href="/channel/{{props.item.creator.slug}}">
                <img :src="props.item.creator.avatar_url" class="w-8 h-8 rounded-full"/>
            </a>
            <p @click="" class="line-clamp-1 ml-2 my-auto text-white text dark:textDark text-lg uppercase font-bold">{{props.item.title}}</p>
        </div>
        <div @click="resetPlayer()"
             class="absolute bottom-5 left-5 flex flex-row gap-x-2 select-none cursor-pointer">
            <font-awesome-icon icon="rotate-right"  class="  h-4 my-auto aspect-square text-white"/>
            <p class="ml-1 text-white text dark:textDark uppercase font-bold">Restart</p>
        </div>
    </div>

</template>

