<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
});
</script>
<template>
    <div class="relative h-full w-full pointer-events-none">
        <Link :href="route('watch.show', {slug: item.slug})" class="absolute w-full h-full z-0 pointer-events-auto">

        </Link>
        <div class="flex flex-row z-10 relative">
            <div class="group relative w-48 lg:w-96 aspect-[21/12] h-max overflow-hidden rounded-md flex-shrink-0">
                    <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="item.thumbnail_url" alt=""/>
            </div>
            <div class="pl-5 w-full">
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">
                        <!-- title -->
                        <span class="pt-1 pr-2 line-clamp-2 overflow-hidden leading-5  mb-0.5 break-all font-bold text-md lg:text-2xl inline-flex" v-text="item.title" :title="item.title"/>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <!--profile picture-->
                                <div v-if="!item.channel_page" class="flex-shrink-0 pr-2">
                                    <Link class="without-ring pointer-events-auto  "
                                          :href="route('channel.show', {slug: item.creator.slug})">
                                        <img v-if="item.creator.avatar_url != null"
                                             class="w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </Link>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  flex flex-col gap-y-1 ">

                                    <!--channel name-->
                                    <Link v-if="!item.channel_page"
                                          :href="route('channel.show', {slug: item.creator.slug})"
                                          class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark   ">
                                        <p class="text-sm sm:text-lg font-bold " v-text="item.creator.name"></p>
                                    </Link>


                                </div>

                            </div>
                        </div>
                        <div class="flex flex-row gap-x-2 mt-2">
                            <ConsistentContentHolder v-if="item.view_count != null"
                                                     class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="item.view_count"/>
                            </ConsistentContentHolder>
                            <!--<ConsistentContentHolder v-if="item.views != null"-->
                            <!--                         class="flex flex-row align-middle justify-center w-max px-5">-->
                            <!--    <p class="text-sm" v-text="item.views"/>-->
                            <!--</ConsistentContentHolder>-->
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
