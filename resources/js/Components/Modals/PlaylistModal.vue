<script setup>
import TickIcon from '#icons/tick.svg';
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/playlists.svg';
import ExitIcon from '#icons/exit.svg';
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { ref, onMounted } from 'vue';
import axios from 'axios';
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
const playlistModalStore = usePlaylistModalStore();
const playlists = ref([]);

onMounted(() => {
    axios.get(route('playlists.modal.refresh'))
        .then(response => {
            playlists.value = response.data['playlists'];
        })
        .catch(error => {
            console.log(error);
        });
});

const name = "PlaylistModal";




</script>



<template>
    <div v-if="!playlistModalStore.showMenu" class="z-10 fixed top-1/2 left-1/2 w-max h-max">
        <OptionHolder class="min-w-64 shadow-md" >
            <!--<div class="w-full flex flex-row p-4 select-none ">-->
            <div class="flex justify-between px-4 py-2  ">
                <p class="text-lg my-auto font-semibold ">Save to...</p>
                <ExitIcon class="w-6 aspect-square ml-auto my-auto cursor-pointer" @click="playlistModalStore.showMenu = !playlistModalStore.showMenu"/>
            </div>
            <!--</div>-->

            <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">
            <label>
                <Option v-for="playlist in playlists"  :key="playlist.id">
                    <Checkbox class="my-auto" :id="'playlist_' + playlist.id" :name="'playlist_' + playlist.id" :value="playlist.id" />
                    <p v-text="playlist.name"> </p>
                </Option>
            </label>

            <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">


            <Option>
                <PlaylistIcon class="w-5 aspect-square pb-0.5" />
                <p>Create new playlist</p>
            </Option>
        </OptionHolder>

    </div>
</template>

