<template>



    <ConsistentContentHolder class="flex h-32 w-full my-auto  dark:border-zinc-900 bg-zinc-50 ">

        <div class=" flex flex-row w-full p-3 py-5 rounded-xl text-center items-center align-middle relative overflow-hidden">

        </div>

    </ConsistentContentHolder>


</template>

<script>
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import Badge from "@/Components/General/Badge.vue";

export default {
name: "CreatorSearchSkeleton",
components: {Badge, ConsistentContentHolder, SubscribeButton},
props: {
creator: {
    type: Object,
    default: null
}
}
}
</script>
