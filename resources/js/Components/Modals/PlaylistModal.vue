<script setup>
import PlaylistIcon from '#icons/add2playlist.svg';
import ExitIcon from '#icons/exit.svg';
import Checkbox from '@/Components/Inputs/Checkbox.vue';
import OptionHolder from '@/Components/Modals/Partials/OptionHolder.vue';
import Option from '@/Components/Modals/Partials/Option.vue';
import { onMounted } from 'vue';
import { vOnClickOutside } from '@vueuse/components';
import { usePlaylistModalStore } from '@/Stores/PlaylistModalStore';
import CreatePlaylistPartial from '@/Components/Modals/Partials/CreatePlaylistPartial.vue';

const playlistModalStore = usePlaylistModalStore();

onMounted(() => {
    playlistModalStore.getMyPlaylists();
});

const name = "PlaylistModal";


const toggle = ((videos_present_in_playlist, playlist_slug) => {
    if (videos_present_in_playlist) {
        playlistModalStore.removeVideosFromPlaylist(playlist_slug)
    } else {
        playlistModalStore.addVideosToPlaylist(playlist_slug)
    }
});


const onClickOutsideHandler = [() => close()];

const close = () => {
    if (playlistModalStore.showMenu) {
        playlistModalStore.showMenu = false;
        playlistModalStore.createPage = false;
    }
};

const togglePlaylistCreate = () => {
    if (playlistModalStore.videoIds.length > 0) {
        playlistModalStore.createPage = !playlistModalStore.createPage;
    } else {
        playlistModalStore.showMenu = false;
    }
};

</script>


<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="playlistModalStore.showMenu"
                class="fixed inset-0 z-[55] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="playlist-modal-title"
            >
                <div
                    class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm dark:bg-black/70"
                    aria-hidden="true"
                    @click="close"
                />
                <div
                    class="relative z-10 w-full max-w-md"
                    v-on-click-outside="onClickOutsideHandler"
                >
                    <OptionHolder class="max-h-[85vh] min-w-[18rem] overflow-hidden">
                        <div
                            class="flex items-center justify-between gap-3 border-b border-zinc-200/80 px-4 py-3 dark:border-zinc-800"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/12 text-cyan-600 dark:bg-cyan-500/18 dark:text-cyan-400"
                                >
                                    <PlaylistIcon class="h-5 w-5" />
                                </span>
                                <p
                                    id="playlist-modal-title"
                                    class="truncate text-base font-semibold tracking-tight text-zinc-900 dark:text-white"
                                >
                                    Save to playlist
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                                @click="close"
                            >
                                <ExitIcon class="h-5 w-5" aria-hidden="true" />
                                <span class="sr-only">Close</span>
                            </button>
                        </div>

                        <div
                            v-if="!playlistModalStore.createPage"
                            class="max-h-52 overflow-y-auto px-1 py-1"
                        >
                            <Option
                                v-for="playlist in playlistModalStore.playlists"
                                :key="playlist.id"
                                class="w-full items-center"
                                @click="toggle(playlist.videos_present_in_playlist, playlist.slug)"
                            >
                                <Checkbox
                                    :checked="playlist.videos_present_in_playlist"
                                    class="my-auto"
                                    :id="'playlist_' + playlist.id"
                                    :name="'playlist_' + playlist.id"
                                    :value="playlist.id"
                                />
                                <p v-text="playlist.name" />
                                <span class="flex-grow" />
                                <font-awesome-icon
                                    v-if="playlist.visibility === 'private'"
                                    :icon="['fas', 'lock']"
                                    class="text-zinc-400"
                                />
                                <font-awesome-icon
                                    v-if="playlist.visibility === 'public'"
                                    :icon="['fas', 'earth-americas']"
                                    class="text-zinc-400"
                                />
                                <font-awesome-icon
                                    v-if="playlist.visibility === 'unlisted'"
                                    :icon="['fas', 'link']"
                                    class="text-zinc-400"
                                />
                            </Option>
                        </div>

                        <div v-if="!playlistModalStore.createPage" class="mx-1 mb-1 border-t border-zinc-200/80 pt-1 dark:border-zinc-800">
                            <Option class="w-full items-center" @click="playlistModalStore.createPage = true">
                                <PlaylistIcon class="h-5 w-5" />
                                <p>Create new playlist</p>
                            </Option>
                        </div>

                        <create-playlist-partial
                            v-if="playlistModalStore.createPage"
                            @backEvent="playlistModalStore.createPage = false"
                            @createEvent="togglePlaylistCreate"
                        />
                    </OptionHolder>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

