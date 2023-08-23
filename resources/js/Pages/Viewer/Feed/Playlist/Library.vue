<script setup>

import Title from "@/Components/General/Title.vue";

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import HistoryBar from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistBar.vue";
import PlaylistBar from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistBar.vue";
</script>

<template>
    <Head title="Library" />

    <ConsistentPadding class="px-12">

        <PlaylistBar :route="route('feed.history')" text="History" />
        <PlaylistBar :route="route('feed.watch-later')" text="Watch Later" />


        <div class="flex flex-row w-full justify-center">
            <div class="flex flex-row  flex-grow my-auto">
                <!--<x-icon name="playlists" class="text dark:textDark w-5 aspect-square mr-2 "/>-->
                <p class="text dark:textDark font-bold text-md
                        uppercase h-full my-auto ">Playlists</p>
            </div>
        </div>
        <!--<x-hr class="my-3 mb-5"/>-->
        <div id="video-holder"
             class="mx-1   flex
                grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4
                ml:grid-cols-4 4xl:grid-cols-6 gap-4">

        </div>



        <PlaylistBar :route="route('feed.liked-videos')" text="Liked Videos" />


    </ConsistentPadding>



</template>
