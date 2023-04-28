<script setup>

import ClockIcon from '#icons/clock.svg';
import TickIcon from '#icons/tick.svg';
import {ref} from "vue";
import {useToastStore} from "@/Stores/ToastStore.js";
import {usePage} from "@inertiajs/vue3";
import axios from 'axios';

const toastStore = useToastStore();

//props below
const props = defineProps({
    video: Object,
    channel_page: Boolean,
});
const name = 'WatchLater';
const inPlaylist = ref(false);
const open = ref(false);
const addToWatchLater = () => {
    if (usePage().props.auth.user == null) {
        //redirect to login page
        // Navigate to the login page URL
        window.location.href = route('login');
    }

    const url = '/playlists/watch_later/videos/' + props.video.id;
    const method = inPlaylist.value ? 'delete' : 'post';

    axios[method](url)
        .then(response => {
            inPlaylist.value = !inPlaylist.value;
            const message = inPlaylist.value ? 'Added to Watch Later' : 'Removed from Watch Later';
            const type = inPlaylist.value ? 'success' : 'error';
            toastStore.add({
                message,
                type,
            });
        })
        .catch(error => {
            console.error(error);
            if (error.response.status === 404) {
                inPlaylist.value = !inPlaylist.value;
                toastStore.add({
                    message: 'This video is not in your Watch Later playlist',
                    type: 'error',
                });
            } else if (error.response.status === 400) {
                inPlaylist.value = !inPlaylist.value;
                toastStore.add({
                    message: 'This video is already in your Watch Later playlist',
                    type: 'error',
                });
            } else if (error.response.status === 429) {
                toastStore.add({
                    message: 'Woah there, slow down!',
                    type: 'error',
                });
            } else {
                toastStore.add({
                    message: 'An error occurred',
                    type: 'error',
                });
            }
        });
}

</script>
<template>
    <div  @mouseenter="open = true" @mouseleave="open = false" @click="addToWatchLater()"
         class="h-7  text-sm px-1.5 z-1 pointer-events-auto cursor-pointer flex flex-row gap-x-3
      group-hover:opacity-100 opacity-0 transition duration-300
       bg-zinc-900/90 rounded ">
        <div v-if="open"
             class="   text-white text-xs font-bold text-center my-auto pl-1 ">
            <p  v-if="inPlaylist">Added to Watch Later</p>
            <p  v-if="!inPlaylist">Add to Watch Later</p>
        </div>
        <div class="  my-auto text-white font-semibold rounded-sm flex flex-col justify-center">
            <TickIcon v-if="inPlaylist" class="w-3.5 my-auto mx-auto"/>
            <ClockIcon v-if="!inPlaylist" class="w-4 my-auto mx-auto"/>
        </div>
    </div>

</template>


