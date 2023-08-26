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

const name = "CreatePlaylistPartial";


const playlistName = ref('');
const playlistVisibility = ref('public');
const playlistNameInput = ref(null);
const visibilityOptions = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
    { value: 'unlisted', label: 'Unlisted' }
];
const emits = defineEmits(['backEvent','createEvent']);


const createPlaylist = () => {
    if (playlistName.value.trim().length > 3) {
        playlistModalStore.createPlaylist(playlistName.value.trim(), playlistVisibility.value);
        emits('createEvent');
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


    <div class="flex flex-col space-y-2 my-2 px-3"  >
        <Option v-if="usePlaylistModalStore().videoIds.length > 0" class="dark:bg-zinc-900  dark:border dark:border-zinc-800"  @click="$emit('backEvent')">
            <font-awesome-icon :icon="['fas', 'square-caret-left']" class="w-4  -mr-1 aspect-square my-auto"/>
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

</template>

