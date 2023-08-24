<script setup>

import Title from "@/Components/General/Title.vue";

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import HistoryBar from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistBar.vue";
import PlaylistBar from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistBar.vue";
import PlaylistsBar from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistsBar.vue";
</script>

<template>
    <Head title="Library" />

    <ConsistentPadding class="px-12">
        <PlaylistBar :href="route('feed.history')" text="History" id="history" key="history" />
        <PlaylistBar :href="route('feed.watch-later')" text="Watch Later" id="watch_later" key="watch_later" />

        <PlaylistsBar text="Playlists"/>

        <PlaylistBar :href="route('feed.liked-videos')" text="Liked Videos" id="liked_videos" key="liked_videos" />



    </ConsistentPadding>



</template>
