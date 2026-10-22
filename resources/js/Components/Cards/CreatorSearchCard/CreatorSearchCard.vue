<template>



    <ConsistentContentHolder class=" relative h-full w-full my-auto  dark:border-zinc-900 bg-zinc-50 ">
        <Link v-bind:href="route('channel.show', creator.slug)" :aria-label="`${creator.name} channel`" class="absolute top-0 left-0 h-full w-full z-0">

        </Link>
        <div class="absolute top-0 left-0 z-10 pointer-events-none flex flex-row w-full px-2 sm:px-8 py-5 rounded-xl text-center items-center align-middle relative overflow-hidden">

            <!--profile picture-->
             <div class="w-14 sm:w-24 rounded-full overflow-hidden flex-shrink-0">
                <img v-if="creator.avatar_url" class="rounded-full mx-auto w-full aspect-square shadow object-cover" v-bind:src="creator.avatar_url" :alt="`${creator.name} avatar`">
                <div v-else class="rounded-full w-full aspect-square bg-zinc-300 dark:bg-zinc-700 flex items-center justify-center font-bold text-2xl" aria-hidden="true">{{ (creator.name || '?').charAt(0).toUpperCase() }}</div>
             </div>
            <div class="flex flex-col pl-3 sm:pl-5 min-w-0 flex-1 text-left">

                <div class="flex flex-row items-center justify-start mb-2 gap-x-3 sm:gap-x-5 min-w-0">
                    <p  class="font-bold text-lg sm:text-2xl truncate min-w-0 text-left" :title="creator.name" v-text="creator.name"/>
                    <SubscribeButton :channel="creator" class="pointer-events-auto inline-flex flex-shrink-0 my-auto" />

                </div>
                    <!-- badges-->
                    <div class="flex flex-row flex-wrap gap-2 min-w-0">

                        <!--Sources badges-->
                        <Badge v-for="source in creator.sources" :key="source" :text="source" :source="source" v-if="creator.sources[0] != null"/>
                        <!--Livestreaming badge-->
                        <Badge text="Livestreaming" v-if="creator.is_live" class="animate-bounce">
                            <font-awesome-icon :icon="['fas', 'fire']" class="w-3 h-3 mr-1.5 text-red-500 animate-pulse"/>
                        </Badge>
                    </div>
            </div>





        </div>

</ConsistentContentHolder>


</template>

<script>
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import Badge from "@/Components/General/Badge.vue";

export default {
name: "CreatorSearchCard",
components: {Badge, ConsistentContentHolder, SubscribeButton},
props: {
    creator: {
        type: Object,
        default: null
    }
}
}
</script>
