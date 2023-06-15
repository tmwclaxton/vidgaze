<template>



    <ConsistentContentHolder class="flex h-48 w-full my-auto  dark:border-zinc-900 bg-zinc-50 p-5">

        <!--<skeleton-div class="w-36 rounded-md aspect-[21/12] h-full"></skeleton-div>-->


    </ConsistentContentHolder>


</template>

<script>
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import Badge from "@/Components/General/Badge.vue";
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

export default {
name: "VideoStreamSearchSkeleton",
components: {SkeletonDiv, Badge, ConsistentContentHolder, SubscribeButton},
props: {
creator: {
    type: Object,
    default: null
}
}
}
</script>
