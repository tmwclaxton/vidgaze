<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";

const name = 'Subscriptions';


</script>



<template>
    <Head>
        <title>Subscriptions</title>
    </Head>


    <ConsistentPadding>
        <div class="flex flex-row w-full  justify-center">
            <div class="flex flex-row  flex-grow my-auto">
                <!--<x-icon name="subscriptions" class="text dark:textDark w-5 aspect-square mr-2 "/>-->
                <p class="text dark:textDark font-bold text-md
                            uppercase h-full my-auto ">Your Subscriptions</p>
            </div>
            <!--<a href="{{ route('feed/channels') }}" class=" text-center my-auto">-->

                <p class="text dark:textDark font-bold text-md
                            uppercase h-full px-3">Manage</p>

            <!--</a>-->
        </div>
        <!--<x-hr class="my-3 mb-5"/>-->
        <!--@if($streams->count() > 0)-->
        <div id="video-holder"
             class="px-3 xs:px-0 flex
                        grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 4xl:grid-cols-6 gap-4 ">


            <!--@foreach($streams as $stream)-->
            <!--<x-box-stream-card :stream="$stream"/>-->
            <!--@endforeach-->

        </div>
        <!--<x-hr class="my-3 mb-5"/>-->
        <!--@endif-->
        <!--@if($videos->count() > 0)-->

        <div id="video-holder"
             class="px-3 xs:px-0 flex
                    grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 4xl:grid-cols-6 gap-4 ">


            <!--@foreach($videos as $video)-->
            <!--<x-box-video-card :video="$video"/>-->
            <!--@endforeach-->

        </div>
        <!--@endif-->
        <!---->
        <!--@if($streams->count() + $videos->count() < 1)-->
        <div class="mt-20">
            <!--<x-error-message :import="false" :explore="true" text="Whoops you have no new content"/>-->
        </div>

    </ConsistentPadding>




</template>
