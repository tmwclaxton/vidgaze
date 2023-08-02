<script setup>
import DotsIcon from '#icons/3dots.svg';
// Import the contentModalStore module
import {useContentModalStore} from "@/Stores/ContentModalStore.js";
import {computed, onMounted, onUnmounted, ref} from "vue";
import Queue from "@/Components/Cards/VideoStreamCards/Partials/Queue.vue";
const contentModalStore = useContentModalStore();

const name = "ShortsCard";
const hideItem = ref(false);
//props below
const props = defineProps({
    item: Object,
    channel_page: Boolean,
});

// Define the setItemId method to call the setItemId method of contentModalStore with the provided id
const itemType = computed(() => {
    return "short";
});
async function setContentModalValues() {
    contentModalStore.item = props.item;
    contentModalStore.itemType = itemType.value;
    await new Promise(resolve => setTimeout(resolve, 100)); // wait for 100 milliseconds
    contentModalStore.setMenuShow(!contentModalStore.showMenu);
};


function hideItemToggle() {
    hideItem.value = !hideItem.value;
}
const dotsIconShow = computed(() => {
    return contentModalStore.item !== null && contentModalStore.item.id === props.item.id && contentModalStore.itemType === itemType.value && contentModalStore.showMenu;
});
</script>

<template>
    <div :id="'box_' + itemType + '_' + item.id" class="relative group overflow-hidden text dark:textDark min-h-64 ">
        <!--hide content hidden button and cover-->
        <div :id="'hide_' + itemType + '_' + item.id" @click="hideItemToggle" class="w-0 h-0 opacity-0 pointer-events-none " ></div>
        <div  v-if="hideItem" class="w-full h-full rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex flex-col align-middle justify-center items-center select-none">
            <p class="text-md font-bold">Content Hidden</p>
            <div  @click="hideItemToggle()" class="text-blue-600 dark:text-blue-400 font-semibold cursor-pointer">
                Show
            </div>
        </div>
        <div  v-if="!hideItem">
            <div class="relative aspect-[10/16] overflow-hidden rounded-xl">
                <a :href="'shorts?short=' + item.slug">
                    <img class="object-none w-full  h-full bg-zinc-900" v-bind:src="item.thumbnail_url"   alt=""/>
                </a>


                <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2
                opacity-0 group-hover:opacity-100 flex duration-300 ease-in-out transition
                p-3  cursor-pointer
                bg-zinc-100/75 dark:bg-black/75 rounded-xl  ">
                    <font-awesome-icon :icon="['fas', 'play']" class="h-4 aspect-square text-zinc-800 dark:text-white" />
                </div>

                <div  class="flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end ">
                    <!--<WatchLater v-if="item.duration != null && useAuthStore().user != null" :item="item" />-->
                    <!--<Queue v-if="item.duration != null" :item="item" />-->
                </div>


            </div>
            <div class="pl-1   py-2">
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">

                    <span
                        class="pt-1  overflow-hidden leading-5 font-bold  text-base  inline-flex">

                        <a :href="'shorts?short=' + item.slug" v-text="item.title" class="pr-2 line-clamp-3"></a>


                    </span>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                                    <a class="without-ring " :href="route('channel.show', {creator: {slug: item.creator.slug}})">

                                        <img v-if="item.creator.avatar_url != null"
                                             class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </a>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  space-y-0    text-xs font-normal">
                                    <a v-if="!channel_page" :href="route('channel.show', {creator: {slug: item.creator.slug}})"
                                       class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark  ">

                                        <p class="text-base font-semibold" v-text="item.creator.name"></p>
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!--3 dots button-->
                    <div :id="'dotsButton_' + itemType + '_' + item.id" class="col-span-1 ml-auto pt-2 w-8 without-ring h-max" >
                        <button @click="setContentModalValues()" class="flex without-ring m-0 mt-0 opacity-90 w-6 rounded-full text-zinc-500 ml-auto   pointer-events-auto">
                            <DotsIcon class="w-6 h-6 opacity-0 duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity ease-in-out" :class="{ 'opacity-100': dotsIconShow}" />
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>
