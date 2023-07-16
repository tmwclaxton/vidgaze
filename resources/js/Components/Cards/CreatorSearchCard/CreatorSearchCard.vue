<template>



    <ConsistentContentHolder class="flex h-full w-full my-auto  dark:border-zinc-900 bg-zinc-50 ">

        <div class=" flex flex-row w-full px-2 sm:px-8 py-5 rounded-xl text-center items-center align-middle relative overflow-hidden">

            <!--profile picture-->
            <Link v-bind:href="'/channel/' + creator.slug" class=" min-w-16 w-24  rounded-full overflow-hidden flex-shrink-0">
                <img class=" rounded-full w-full  mx-auto w-full aspect-square shadow " v-bind:src="creator.avatar_url">
            </Link>

            <div class="flex flex-col pl-5 align-items-start text-left">

                <div class="flex flex-row align-middle justify-center mb-2 gap-x-5">
                    <p  class="font-bold text-2xl break-all line-clamp-1  text-left" v-text="creator.name"/>
                    <SubscribeButton :channel="creator" class="  inline-flex text-xl my-auto " />

                </div>

                <!-- badges-->
                <div class="flex flex-row flex-wrap gap-2  ">

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
