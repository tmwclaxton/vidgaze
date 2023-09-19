<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    item_analytics: {
        type: Object,
        required: true
    }
});
</script>
<template>
    <div class="relative h-max w-full pointer-events-none overflow-hidden">
        <Link :href="route('studio.video.edit', {slug: item.slug})" class="absolute w-full h-full z-0 pointer-events-auto">

        </Link>
        <div class="flex flex-col md:flex-row z-10 relative h-full w-full overflow-hidden">
            <div class="group relative w-36 lg:w-72 aspect-[21/12] h-max overflow-hidden rounded-md flex-shrink-0">
                    <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="item.thumbnail_url" alt=""/>
            </div>
            <div class="md:pl-5 md:flex-grow overflow-hidden ">
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">
                        <!-- title -->
                        <span class="pt-1 pr-2 line-clamp-2 overflow-hidden leading-5  mb-0.5 break-all font-bold text-md lg:text-2xl inline-flex" v-text="item.title" :title="item.title"/>

                        <div class="flex flex-row flex-wrap gap-2 mt-2 w-full ">
                            <ConsistentContentHolder v-if="item.view_count != null"
                                                     class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="item.view_count"/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder v-if="item_analytics.total_watch_time != null"
                                                     class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="item_analytics.total_watch_time"/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder v-if="item_analytics.avg_view_duration != null"
                                                     class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="item_analytics.avg_view_duration"/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder v-if="item_analytics.avg_percentage_watched != null"
                                                     class="flex flex-row flex-wrap break-all align-middle justify-center w-max px-5">
                                <p class="text-sm break-all" v-text="item_analytics.avg_percentage_watched"/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder v-if="item_analytics.ctr != null"
                                                     class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="item_analytics.ctr"/>
                            </ConsistentContentHolder>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
