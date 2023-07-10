<script setup>
import TickIcon from '#icons/tick.svg';
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/add2playlist.svg';
import ExitIcon from '#icons/exit.svg';
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { ref, onMounted } from 'vue';
import { vOnClickOutside } from '@vueuse/components';
import TextInput from "@/Components/Inputs/TextInput.vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
const playlistModalStore = usePlaylistModalStore();
import {useToastStore} from "@/Stores/ToastStore";
const toastStore =  useToastStore();
let showPlaylistCreate = ref(false);

onMounted(() => {
    playlistModalStore.getPlaylists();
});

const name = "PlaylistModal";


const toggle = ((videos_present_in_playlist, playlist_id) => {
    if (videos_present_in_playlist) {
        playlistModalStore.removeVideosFromPlaylist(playlist_id)
    } else {
        playlistModalStore.addVideosToPlaylist(playlist_id)
    }
});


const ignoreElRef = ref();
const onClickOutsideHandler = [
    (ev) => {
        // console.log(ev)
        close();
    },
    { ignore: [ignoreElRef] }
]

const close = () => {
    if (playlistModalStore.showMenu) {
        playlistModalStore.showMenu = false;
        showPlaylistCreate.value  = false;
    }
}
const playlistName = ref('');
const playlistVisibility = ref('public');
const playlistNameInput = ref(null);
const visibilityOptions = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
    { value: 'unlisted', label: 'Unlisted' }
];

const createPlaylist = () => {
    if (playlistName.value.trim().length > 3) {
        playlistModalStore.createPlaylist(playlistName.value.trim(), playlistVisibility.value);
        showPlaylistCreate.value = false;
        // toastStore.add({
        //     message:"Playlist created",
        //     type: 'success',
        // });
    } else {
        toastStore.add({
            message:" Playlist name must be at least 4 characters long " +  playlistName.value.trim() + playlistVisibility.value,
            type: 'warning',
        });
        // Give focus to the playlist name input
        playlistNameInput.value.focus();
    }
}

</script>


<template>
    <div v-if="playlistModalStore.showMenu"  class="pointer-events-none z-40 absolute left-1/2 right-1/2 flex-grow h-max w-max flex flex-row justify-center">
        <div class="pointer-events-none  fixed my-auto inset-y-0 h-max flex">
            <OptionHolder class="min-w-64 shadow-md h-max mx-auto pointer-events-auto" v-on-click-outside="onClickOutsideHandler" >
                <!--<div class="w-full flex flex-row p-4 select-none ">-->
                <div class="flex justify-between px-4 py-2  ">
                    <p class="text-lg my-auto font-semibold ">Save to...</p>
                    <ExitIcon class="w-6 aspect-square ml-auto my-auto cursor-pointer" @click="close"/>
                </div>
                <!--</div>-->
                <div v-if="!showPlaylistCreate" class="h-52 overflow-y-auto ">
                    <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-1">
                    <Option class="items-center w-full" v-for="playlist in playlistModalStore.playlists"  :key="playlist.id" @click="toggle(playlist.videos_present_in_playlist,playlist.id)">
                        <Checkbox :checked="playlist.videos_present_in_playlist" class="my-auto" :id="'playlist_' + playlist.id" :name="'playlist_' + playlist.id" :value="playlist.id" />
                        <p v-text="playlist.name"/>
                        <span class="flex-grow"/>
                        <font-awesome-icon :icon="['fas', 'lock']" v-if="playlist.visibility === 'private'"/>
                        <font-awesome-icon :icon="['fas', 'earth-americas']" v-if="playlist.visibility === 'public'"/>
                        <font-awesome-icon :icon="['fas', 'link']" v-if="playlist.visibility === 'unlisted'"/>
                    </Option>
                </div>

                <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">

                <Option v-if="!showPlaylistCreate" @click="showPlaylistCreate = true">
                    <PlaylistIcon class="w-5 aspect-square " />
                    <p>Create new playlist</p>
                </Option>

                <div class="flex flex-col space-y-2 my-2 px-3" v-if="showPlaylistCreate" >
                    <Option class="  dark:bg-zinc-900  dark:border dark:border-zinc-800"  @click="showPlaylistCreate = false">
                        <font-awesome-icon :icon="['fas', 'square-caret-left']" class="w-4  -mr-1 aspect-square my-auto"/>
                        <!--<font-awesome-icon :icon="['fas', 'pen']" class="w-4 pb-0.5 -mr-0.5 aspect-square my-auto"/>-->
                        <p class="">Back</p>
                    </Option>

                    <TextInput
                        class="p-2 block w-full px-4 py-1  font-semibold  text-zinc-700 dark:text-zinc-200
                            rounded-lg  bg-white dark:bg-zinc-900 placeholder-zinc-400 dark:placeholder-zinc-500 "
                        v-model="playlistName" name="Enter playlist name..." title="Name" maxlength="100" placeholder="Enter playlist name..." ref="playlistNameInput" />
                    <SelectInput class="dark:bg-zinc-900" v-model="playlistVisibility" name="visibility" title="Visibility" @update:model-value="value => playlistVisibility = value" :options="visibilityOptions" />

                    <Option @click="createPlaylist" class="justify-center bg-zinc-100 dark:bg-vidgaze-blue/70 ">
                        <font-awesome-icon :icon="['fas', 'pen']" class="w-4 pb-0.5 -mr-0.5 aspect-square my-auto"/>
                        <p class="">Create</p>
                    </Option>

                </div>

            </OptionHolder>

        </div>

    </div>
</template>

