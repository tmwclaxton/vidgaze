
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {onMounted, ref} from "vue";
import Badge from "@/Components/General/Badge.vue";

const name = 'Channel';
const channel = ref(null);
const channelLoading = ref(true);
const props = defineProps({
    slug: {
        type: String,
        required: true
    }
});

const fetchChannel = async () => {
    console.log('fetching channel');
    channelLoading.value = true;
    axios.get(route('api.creator.show', {slug: props.slug}))
        .then((response) => {
            channel.value = response.data.creator
            channelLoading.value = false;
        })
        .catch((error) => {
            console.log(error);
        });
};

onMounted(() => {
    fetchChannel();
});

</script>



<template>
    <Head>
        <title></title>
    </Head>

        <div v-if="channel" class="flex flex-col flex-grow ">
            <div class="relative flex flex-row  bg-zinc-50  max-h-64 overflow-hidden">
                <img class=" flex-grow object-cover"
                     v-bind:src="channel.banner_url" alt="">
                    <div class=" cursor-pointer absolute bg-zinc-900 border border-zinc-600 p-2 px-4 bg-opacity-70 rounded
                        top-5 right-5 font-bold gap-x-3 flex flex-row">
                        <font-awesome-icon :icon="['fas', 'share-alt']" class="w-4 text-white my-auto"/>
                        <p class="hidden md:flex opacity-100 text-white">Share VidGaze Channel</p>
                    </div>
            </div>

            <div class="   pt-5 lg pb-3 px-5 lg:px-10 generic-background_2 dark:generic-background-dark_2  ">
                <div class="flex flex-row justify-center sm:justify-start px-2 overflow-hidden h-max">

                    <img class="bg-white   flex-shrink-0  h-20 aspect-square rounded-full "
                        v-bind:src="channel.avatar_url" alt="">

                    <div class="flex flex-col sm:flex-row  overflow-hidden w-full h-full ">
                        <div class=" flex flex-col w-full  sm:w-64 md:w-64 lg:w-full flex-shrink md:my-auto pl-5">
                            <p class=" subheading text-xl break-words" v-text="channel.name"></p>
                            <span class="" v-text="channel.subscribers_count"></span>
                            <div class="mt-1 inline-flex space-x-1">
                                <Badge v-for="source in channel.sources" :key="source" :text="source" :source="source" v-if="channel.sources[0] != null"/>
                            </div>
                        </div>
                        <div
                            class="flex flex-col pt-2 sm:pt-1 md:pt-0 md:flex-row gap-x-2 space-y-2 md:space-y-0 md:my-auto sm:ml-auto ">
                            <!--<a href="{{ route('studio.customise.edit') }}"-->
                            <!--   class="ml-5 sm:ml-0 channel_blue_buttons dark:channel_blue_buttons-dark w-max">Customise-->
                            <!--    Channel</a>-->
                            <!--<a href="{{ route('studio/content') }}"-->
                            <!--   class="ml-5 sm:ml-0  channel_blue_buttons dark:channel_blue_buttons-dark w-max">Manage-->
                            <!--    Videos</a>-->
                            <!--<div class="w-max pl-5">-->
                            <!--    <livewire:subscribe-button :creator="$creator"/>-->
                            <!--</div>-->
                            <!--<p @click=" flash('Login to subscribe');" class="  subscribe  ">Subscribe</p>-->

                        </div>
                         <!--this is here for design fix -->
                        <div class="h-20 hidden sm:flex">
                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="z-index: 1"
                 id="channelHeader" class="   z-10 sm:px-5 lg:px-10 bg-zinc-50  dark:bg-zinc-900  text-sm font-bold text-center text-zinc-500  dark:text-zinc-200 ">
                <div  class="flex flex-row -mb-px   justify-start overflow-x-scroll xs:overflow-hidden w-full">

                </div>
            </div>

            <div class="pt-5 px-5 lg:px-10 pb-10 h-full min-h-screen ">



            </div>
        </div>




</template>
