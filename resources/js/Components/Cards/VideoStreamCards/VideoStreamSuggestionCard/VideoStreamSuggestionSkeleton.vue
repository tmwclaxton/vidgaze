<script setup>
import CornerInfo from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import WatchLater from "@/Components/Cards/VideoStreamCards/Partials/WatchLater.vue";
import Queue from "@/Components/Cards/VideoStreamCards/Partials/Queue.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

const name = 'VideoStreamSuggestionSkeleton';

</script>

<template>
    <div class="relative flex   group z-0">

        <div class="relative aspect-[21/12] overflow-hidden rounded-md flex-shrink-0 h-24 ">
            <SkeletonDiv class="w-full h-full"/>


        </div>

        <div class="w-full ">
                <div class="w-full h-10 flex flex-col gap-y-2 align-bottom leading-none pl-2 ">
                    <SkeletonDiv class="w-2/3 h-4"/>
                    <SkeletonDiv class="w-1/3 h-6"/>

                </div>
        </div>

    </div>

</template>
