<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {onMounted, ref} from "vue";
const name = 'ChannelOverview';

const views = ref(null);
const viewDuration = ref(null);
onMounted(() => {
    axios.get(route('api.studio.analytics')).then((response) => {
        views.value = response.data.views;
        viewDuration.value = response.data.viewDuration;
    })
})

</script>

<template>
    <ConsistentContentHolder class="p-5 h-full">
        <div class="flex flex-col" v-if="useAuthStore().user">
            <div class="flex flex-col items-center justify-center">
                <img v-bind:src="useAuthStore().user.creator.avatar_url"
                     class="w-24 aspect-square rounded-full mb-3 shadow">
                <p class="font-bold text-lg" >Your Channel</p>
                <p class="font-light text-xl" v-text="useAuthStore().user.creator.name"></p>
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-600 my-2"></div>

            <div class="flex flex-col">
                <p class="font-bold mb-3 ">VidGaze Channel Analytics</p>

                <div class=" flex flex-row flex-wrap gap-2 w-full">
                    <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                        <p class="text-sm" v-text="useAuthStore().user.creator.subscriber_count "/>
                    </ConsistentContentHolder>
                    <ConsistentContentHolder v-if="viewDuration != null"
                        class="flex flex-row align-middle justify-center w-max px-5">
                        <p class="text-sm" v-text="viewDuration"/>
                    </ConsistentContentHolder>
                    <ConsistentContentHolder v-if="views != null"
                        class="flex flex-row align-middle justify-center w-max px-5">
                        <p class="text-sm" v-text="views"/>
                    </ConsistentContentHolder>
                </div>
            </div>
        </div>
    </ConsistentContentHolder>
</template>



