<template>


    <div class="flex flex-col gap-y-2">



    <ConsistentContentHolder class="flex h-full my-auto  border-none bg-zinc-50 ">

        <div class="w-full flex flex-col gap-y-3 p-3 py-5 rounded-xl text-center items-center align-middle relative">



            <!--profile picture-->
            <a v-bind:href="'/channel/' + creator.slug" class=" p-2 mx-auto w-full rounded-full overflow-hidden flex-shrink-0">
                <img class=" rounded-full w-full  mx-auto w-full aspect-square shadow" v-bind:src="creator.avatar_url">

            </a>


            <p  class="font-bold text-2xl mx-2 break-all line-clamp-1" v-text="creator.name"/>

            <!-- badges-->
            <div class="flex justify-center -mt-1">
                <div class="flex flex-row flex-wrap mx-auto gap-2 justify-center justify-items-center align-items-center align-middle ">
                    <!--Livestreaming badge-->
                    <Badge text="Livestreaming" v-if="creator.is_live">
                        <font-awesome-icon :icon="['fas', 'fire']" class="w-3 h-3 mr-1.5 text-red-500 animate-pulse"/>
                    </Badge>

                    <!--Sources badges-->
                    <Badge v-for="source in creator.sources" :key="source" :text="source" :source="source" v-if="creator.sources[0] != null"/>
                </div>
            </div>



            <SubscribeButton :channel="creator" class="mt-3" />


</div>

</ConsistentContentHolder>


    </div>
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
