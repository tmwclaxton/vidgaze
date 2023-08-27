<script setup>
import {usePlayerStore} from "@/Stores/PlayerStore";
import {computed, onMounted, onUnmounted, ref} from "vue";
import SuggestionsScreen from "@/Pages/Viewer/Watch/Partials/SuggestionsScreen/SuggestionsScreen.vue";
import {useQueueStore} from "@/Stores/QueueStore";
import UpNextVideo from "@/Pages/Viewer/Watch/Partials/UpNextVideo.vue";
const playerStore = usePlayerStore();
const timer = ref(8);
const myTimer = ref(null);
const name = "EndScreen";

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
})


const playNext = () => {
    useQueueStore().changeIndex(useQueueStore().index + 1);
}

const resetPlayer = async () => {
    usePlayerStore().destroyPlayers(true,false).then(() => {
        usePlayerStore().buildPlayer('watch_player', props.item, 0, true, false);
    });
}

const nextUpScreen = ref(true);

onMounted(() => {
    // console.log("mounted end screen");
    myTimer.value = setInterval(() => {
        if (nextUpScreen.value === false || useQueueStore().nextItem === null) {
            clearInterval(myTimer.value);
            // console.log("cleared interval");
            return;
        }
        timer.value--;
        if (timer.value === 0) {
            // console.log("play next");
            clearInterval(myTimer.value);
            playNext();
            return;
        }
    }, 1000);
});

onUnmounted(() => {
    clearInterval(myTimer.value);
});

const nextItem = useQueueStore().nextItem;
</script>

<template>
    <div id="endScreen"
         class="w-full h-full  without-ring flex relative duration-600 transition ">
        <div v-if="nextItem && nextUpScreen"
            id="nextUpScreen"
             class=" mx-auto mt-5 sm:my-auto w-96  flex flex-col gap-y-3">
            <p class="text-left text text-zinc-400 font-bold ">Up next in
                <span class="text-white" id="myTimer" v-text="timer"></span>
            </p>
            <div class="  ">

                <!--nex video-->
                <UpNextVideo v-if="nextItem !== null" :item="nextItem"/>

            </div>
            <div id="buttons" class="  flex flex-row gap-x-4 select-none ">

                <div @click="nextUpScreen = false"
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

        <Link :href="route('channel.show', {slug: props.item.creator.slug})" class="absolute top-5 left-5 hidden sm:flex flex-row select-none cursor-pointer">
            <img :src="props.item.creator.avatar_url" class="w-8 h-8 rounded-full"/>
            <p  class="line-clamp-1 ml-2 my-auto text-white text dark:textDark text-lg uppercase font-bold" v-text="props.item.title"/>
        </Link>
        <div @click="resetPlayer"
             class="absolute bottom-5 left-5 flex flex-row gap-x-2 select-none cursor-pointer">
            <font-awesome-icon icon="rotate-right"  class="  h-4 my-auto aspect-square text-white"/>
            <p class="ml-1 text-white text dark:textDark uppercase font-bold">Restart</p>
        </div>
    </div>

</template>

