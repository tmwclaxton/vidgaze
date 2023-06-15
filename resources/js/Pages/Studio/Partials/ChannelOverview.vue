<template>
    <ConsistentContentHolder class="p-5 h-full">
        <div class="flex flex-col items-center justify-center">
            <img v-bind:src="$page.props.auth.user.data.creator.avatar_url"
                 class="w-24 aspect-square rounded-full mb-3 shadow">
            <p class="font-bold text-lg" >Your Channel</p>
            <p class="font-light text-xl" v-text="$page.props.auth.user.data.creator.name"></p>
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2"></div>

        <div class="flex flex-col">
            <p class="font-bold mb-3 ">VidGaze Channel Analytics</p>

            <div class=" flex flex-row flex-wrap gap-2 w-full">
                <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                    <p class="text-sm" v-text="$page.props.auth.user.data.creator.subscriber_count "/>
                </ConsistentContentHolder>
                <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                    <p class="text-sm" v-text="'3:53 Avg View Duration '"/>
                </ConsistentContentHolder>
                <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                    <p class="text-sm" v-text="'25 Views this Month'"/>
                </ConsistentContentHolder>
            </div>
        </div>
    </ConsistentContentHolder>
</template>

<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
const name = 'ChannelOverview';
</script>

<style scoped>

</style>
