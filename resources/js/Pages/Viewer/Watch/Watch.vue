

<script setup>
import {computed, onMounted, onUnmounted, ref, watch} from "vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import {usePlayerStore} from "@/Stores/PlayerStore";
import {useQueueStore} from "@/Stores/QueueStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";

import ShareIcon from '~/images/icons/share.svg'
import LibraryIcon from '~/images/icons/library.svg';
import TheatreIcon from '~/images/icons/expand.svg';
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import {useContentModalStore} from "@/Stores/ContentModalStore";

import TertiaryButton from "@/Components/Buttons/TertiaryButton.vue";
import LikeDislikeButtons from "@/Components/Buttons/LikeDislikeButtons.vue";
import CommentSection from "@/Components/CommentSection/CommentSection.vue";
import FeatureCreatorButton from "@/Components/Buttons/FeatureCreatorButton.vue";
import {useNavStore} from "@/Stores/NavStore";

import EndScreen from "@/Pages/Viewer/Watch/Partials/EndScreen.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import WatchQueue from "@/Pages/Viewer/Watch/Partials/WatchQueue.vue";
import SuggestedVideos from "@/Pages/Viewer/Watch/Partials/SuggestedVideos.vue";
import ExternalCommentSection from "@/Components/CommentSection/ExternalCommentSection.vue";
import AwardsDropdown from "@/Components/Dropdown/AwardsDropdown.vue";
import {usePinModalStore} from "@/Stores/PinModalStore";

const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const playlistModalStore = usePlaylistModalStore();
const shareModalStore = useShareModalStore();
const contentModalStore = useContentModalStore();
const pinModalStore = usePinModalStore();
const NavStore = useNavStore();
const authStore = useAuthStore();
const name = 'Watch';

const theatre = ref(false);
const item = ref(null);

const comments = ref(null)
const isDescriptionCollapsed = ref(true);
const showCommentSection = ref(false);
const playlistToggled = ref(false); // can't seem to get it work directly with the store
const showShare = ref(false);
const showMoreDescriptionButton = ref(false);
const suggestions = ref(null);
const ready = ref(false);

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    slug: {
        type: String,
        required: true
    },
});

const showWatchingCount = computed(() => {
    if (!item.value || !ready.value) {
        return false;
    }
    if (item.value.type === 'stream') {
        return item.value.live_viewer_count && item.value.live_viewer_count !== '0';
    }
    const raw = item.value.unadulterated?.live_viewer_count;
    return typeof raw === 'number' && raw > 0;
});

const actionBtnClass =
    'group flex shrink-0 flex-row items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800 cursor-pointer';

/** Normal mode keeps page inset; theatre strips horizontal padding on the grid (with ! so responsive padding cannot linger). */
const watchGridLayoutClass = computed(() =>
    theatre.value
        ? 'w-full min-w-0 max-w-none gap-0 !px-0 sm:!px-0 lg:!px-0 mx-0'
        : 'mx-auto w-full max-w-[1920px] gap-6 px-4 pb-10 pt-2 sm:px-6 lg:gap-8 lg:px-10'
);

/** Defer theatre toggle so embed/iframes can finish layout before Vue repatches class trees (avoids patchClass el=null on Vue 3.2). */
function toggleTheatre() {
    requestAnimationFrame(() => {
        theatre.value = !theatre.value;
    });
}

function exitTheatreOnResize() {
    if (theatre.value) {
        theatre.value = false;
    }
}

const playerShellId = computed(() => {
    const id = playerStore.refreshFrontEndComponent;
    return id && String(id).length ? id : undefined;
});

function togglePlaylistModal()  {
    if (props.type !== 'video') {
        console.log('not a video');
        return;
    }
    playlistModalStore.videoIds = [item.value.id];
    if (!playlistToggled.value) {
        playlistModalStore.getMyPlaylists();
        playlistModalStore.showMenu = true;
    } else {
        playlistModalStore.showMenu = false;
    }
    playlistToggled.value = !playlistToggled.value;
}

function togglePinModal() {
    console.log('toggling pin modal');
    if (authStore.user === null) {
        console.log('not logged in');
        return;
    }
    if (props.type !== 'video') {
        console.log('not a video');
        return;
    }
    pinModalStore.showMenu = true;
    pinModalStore.video_id = item.value.id;
    pinModalStore.getPinDetails();

}

const share = () => {
    if (showShare.value) {
        shareModalStore.showMenu = false;
    } else {
        contentModalStore.itemType = item.value.type;
        contentModalStore.item = item.value;
        contentModalStore.shareContent();
    }
    showShare.value = !showShare.value;
};

function shouldShowMoreDescriptionButton() {
    if (document.getElementById('description') === null) {
        return false;
    }
    const el = document.getElementById('description');
    const divHeight = el.offsetHeight;
    const lineHeight = parseInt(el.style.lineHeight);
    return divHeight / lineHeight >= 3;
}

onMounted(  () => {
    window.addEventListener('resize', exitTheatreOnResize, { passive: true });

    usePlayerStore().destroyPlayers().then(async () => {
        useQueueStore().playlistLoading = false; // this is used to stop miniplayer from showing up on the playlist page too soon

        // close sidebar
        NavStore.showingNavigationDropdown = false;

        // get video / stream details
        if (props.type === 'video') {
            console.log('getting video');
            item.value = await useContentRoutesStore().getVideo(props.slug);
        }
        if (props.type === 'stream') {
            console.log('getting stream');
            item.value = await useContentRoutesStore().getStream(props.slug);
        }
    });
});


// watch item for changes
watch(item, async (newItem) => {
    ready.value = false;
    if (newItem !== null) {
        showMoreDescriptionButton.value = shouldShowMoreDescriptionButton();

        // if not in queue build player like normal
        if (queueStore.items.length === 0 || queueStore.currentItem.external_id === null || queueStore.currentItem.external_id !== item.value.external_id) {
            await usePlayerStore().buildPlayer('watch_player', item.value, 0, true,true);
        } else {
            // console.log('building queue player' + [useQueueStore().currentPlayer.currentTime]);
            // if in queue build player with time
            await usePlayerStore().buildPlayer('watch_player', item.value, queueStore.currentPlayer.currentTime, true,true);
        }
        ready.value = true;
    }
});

// watch current

onUnmounted(() => {
    window.removeEventListener('resize', exitTheatreOnResize);

    ready.value = false;
    // if the queue has items destroy the players and rebuild the player with the current item in the mini player
    if (queueStore.items.length > 0) {
        playerStore.destroyPlayers().then(() => {
            queueStore.rebuildPlayer();
        });
    } else {
        console.log('destroying all players');
        // otherwise destroy all players and remove the players in playerstore entirely
        playerStore.destroyPlayers(true);
    }
});
</script>

<template>
    <!-- Single root avoids Vue 3.2 fragment unmount crash when the layout forces remount via :key. -->
    <div class="min-w-0">
        <SeoHead
            :title="item?.title || 'Watch'"
            :description="item?.description || ''"
            :image="item?.thumbnail_url || null"
            :og-type="item ? 'video.other' : 'website'"
        />

        <AwardsDropdown v-if="authStore.showAwardDropdown && item" :type="item.type" :object_id="item.id" />
        <div class="grid h-full grid-cols-12 grid-flow-row-dense" :class="watchGridLayoutClass">
            <!--player with theatre mode-->
            <div
                :class="[
                    theatre ? 'col-span-12 w-full min-w-0' : 'col-span-12 lg:col-span-8',
                    theatre ? 'gap-y-0' : 'gap-y-5',
                ]"
                class="relative flex min-w-0 w-full flex-col"
            >
                <!--
                  Black pillarbox/letterbox: outer caps height; inner stays 16:9. In theatre, the black
                  shell is full viewport width (w-screen + center breakout) so no page background shows
                  beside the matte.
                -->
                <div
                    :id="playerShellId"
                    class="flex max-h-[calc(100vh-10rem)] justify-center overflow-hidden bg-black"
                    :class="
                        theatre
                            ? 'relative left-1/2 w-screen max-w-[100vw] shrink-0 -translate-x-1/2 rounded-none shadow-none ring-0'
                            : 'w-full rounded-2xl shadow-xl shadow-black/25 ring-1 ring-zinc-900/15 dark:ring-white/10'
                    "
                >
                    <div
                        class="aspect-video w-full max-w-[min(100%,calc((100vh-10rem)*16/9))] bg-black"
                    >
                        <!--video player-->
                        <div
                            id="watch_player"
                            :class="
                                item &&
                                playerStore.players.length > 0 &&
                                !(playerStore.findPlayer(item.external_id)?.endScreen)
                                    ? 'without-ring relative flex h-full w-full bg-black'
                                    : 'opacity-0'
                            "
                        />

                        <!--end screen-->
                        <EndScreen
                            v-if="ready && item && playerStore.findPlayer(item.external_id)?.endScreen"
                            :item="item"
                            class="h-full w-full"
                        />
                    </div>
                </div>

                <div
                    v-if="item !== null"
                    :class="
                        theatre ? 'w-full max-w-none !px-0 pb-10 pt-4 sm:pt-5 sm:!px-0 lg:!px-0' : ''
                    "
                >
                    <!--video details-->
                    <div class="w-full">
                        <h1
                            class="line-clamp-2 text-xl font-bold leading-snug tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-2xl"
                            v-text="ready ? item.title : 'Loading...'"
                        />

                        <div
                            v-if="ready"
                            class="mt-3 flex flex-col gap-4 border-b border-zinc-200/90 pb-4 dark:border-zinc-800 lg:flex-row lg:items-start lg:justify-between"
                        >
                            <div class="flex min-w-0 flex-col gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <div v-if="item.type === 'video'" class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span v-text="item.view_count"/>
                                    <span class="text-zinc-400 dark:text-zinc-600" aria-hidden="true">·</span>
                                    <span v-text="item.time_published"/>
                                    <template v-if="item.category !== null && item.category.slug !== undefined">
                                        <span class="text-zinc-400 dark:text-zinc-600" aria-hidden="true">·</span>
                                        <Link
                                            class="font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400 dark:hover:text-violet-300"
                                            :href="route('category.show', { slug: item.category.slug })"
                                            v-text="item.category.name"
                                        />
                                    </template>
                                </div>

                                <div v-else-if="item.type === 'stream'" class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="item.is_live"
                                        class="inline-flex items-center rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-white"
                                    >
                                        Live
                                    </span>
                                    <span v-if="item.viewers" v-text="item.viewers"/>
                                    <template v-if="item.category !== null && item.category.slug !== undefined">
                                        <span class="text-zinc-400 dark:text-zinc-600" aria-hidden="true">·</span>
                                        <Link
                                            class="font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400 dark:hover:text-violet-300"
                                            :href="route('category.show', { slug: item.category.slug })"
                                            v-text="item.category.name"
                                        />
                                    </template>
                                </div>

                                <div
                                    v-if="showWatchingCount"
                                    class="inline-flex w-max items-center gap-2 rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300"
                                >
                                    <span class="relative flex h-2 w-2 shrink-0">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"
                                        />
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"/>
                                    </span>
                                    <span>{{ item.live_viewer_count }} watching</span>
                                </div>
                            </div>

                            <div
                                v-if="ready && item"
                                class="flex w-full flex-wrap items-center gap-1 lg:ml-auto lg:w-auto lg:justify-end"
                            >
                                <FeatureCreatorButton
                                    v-if="authStore.user && (authStore.user.creator.role === 'moderator' || authStore.user.creator.role === 'admin')"
                                    :creator_id="item.creator.id"
                                />

                                <TertiaryButton v-if="item.type === 'video'" class="shrink-0">
                                    <LikeDislikeButtons :item="item" :orientationVertical="false"/>
                                </TertiaryButton>

                                <button type="button" :class="actionBtnClass" @click="share">
                                    <ShareIcon class="h-5 w-5 shrink-0 opacity-80 group-hover:opacity-100"/>
                                    <span>Share</span>
                                </button>

                                <button
                                    v-if="item.type === 'video' && authStore.user"
                                    type="button"
                                    :class="actionBtnClass"
                                    @click="togglePlaylistModal()"
                                >
                                    <LibraryIcon class="h-5 w-5 shrink-0 opacity-80 group-hover:opacity-100"/>
                                    <span>Save</span>
                                </button>

                                <button
                                    v-if="
                                        authStore.user &&
                                        (authStore.user.creator.role === 'moderator' || authStore.user.creator.role === 'admin') &&
                                        item.type === 'video'
                                    "
                                    type="button"
                                    :class="actionBtnClass"
                                    @click="togglePinModal()"
                                >
                                    <FontAwesomeIcon class="h-4 w-4 shrink-0 opacity-80 group-hover:opacity-100" :icon="['fas', 'map-pin']"/>
                                    <span>Pin</span>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    :aria-label="theatre ? 'Exit theatre mode' : 'Theatre mode'"
                                    @click="toggleTheatre"
                                >
                                    <TheatreIcon class="h-5 w-5" aria-hidden="true"/>
                                    <span class="max-sm:sr-only" v-text="theatre ? 'Exit theatre' : 'Theatre'"/>
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="ready"
                            class="mt-5 rounded-2xl border border-zinc-200/90 bg-zinc-50/90 p-4 dark:border-zinc-800 dark:bg-zinc-900/40 sm:p-5"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <Link :href="route('channel.show', item.creator.slug)" class="shrink-0">
                                        <img
                                            class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow-md dark:ring-zinc-800"
                                            :src="item.creator.avatar_url"
                                            alt=""
                                        />
                                    </Link>
                                    <div class="min-w-0 flex-1">
                                        <Link
                                            :href="route('channel.show', item.creator.slug)"
                                            class="block truncate text-base font-bold text-zinc-900 hover:text-violet-600 dark:text-zinc-100 dark:hover:text-violet-400"
                                        >
                                            <span v-text="item.creator.name"/>
                                        </Link>
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400" v-text="item.creator.subscriber_count"/>
                                    </div>
                                </div>
                                <div class="shrink-0 sm:pl-2">
                                    <SubscribeButton :channel="item.creator"/>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-zinc-200/80 pt-4 text-sm leading-relaxed text-zinc-700 dark:border-zinc-800 dark:text-zinc-300">
                                <div
                                    id="description"
                                    style="line-height: 1.5"
                                    :class="{ 'line-clamp-3': isDescriptionCollapsed }"
                                    v-html="item.description"
                                />
                                <button
                                    v-if="showMoreDescriptionButton"
                                    type="button"
                                    class="mt-3 text-xs font-bold uppercase tracking-wide text-violet-600 hover:text-violet-500 dark:text-violet-400"
                                    @click="isDescriptionCollapsed = !isDescriptionCollapsed"
                                    v-text="!isDescriptionCollapsed ? 'Show less' : 'Show more'"
                                />
                            </div>
                        </div>

                        <RowDivider v-if="item.type === 'video'" class="mt-6"/>
                    </div>

                    <TertiaryButton
                        v-if="!showCommentSection"
                        class="mt-6 w-full text-center lg:hidden"
                        @click="showCommentSection = !showCommentSection"
                    >
                        <p class="w-full text-center">Open comments</p>
                    </TertiaryButton>

                    <CommentSection
                        v-if="item.type !== 'stream' && item.creator !== undefined"
                        :item="item"
                        class="mt-6"
                        :class="[showCommentSection ? 'flex' : 'hidden lg:flex']"
                    />

                    <RowDivider :class="[theatre ? 'mt-6 flex' : 'mt-6 flex lg:hidden']"/>
                </div>
            </div>

            <!--video suggestions-->
            <div
                class="relative flex min-h-screen w-full min-w-0 flex-col gap-6"
                :class="
                    theatre
                        ? 'col-span-12 w-full max-w-none !px-0 pb-10 sm:!px-0 lg:!px-0'
                        : 'col-span-12 lg:col-span-4 lg:sticky lg:top-20 lg:self-start'
                "
            >
                <WatchQueue v-if="props.type !== 'stream' && item" :item="item" :ready="ready"/>

                <SuggestedVideos
                    v-if="item !== null && props.type !== 'stream'"
                    :video="item"
                    :creator="item.creator"
                    :ready="ready"
                />

                <div
                    v-if="item !== null && props.type === 'stream'"
                    class="flex h-[calc(100vh-10rem)] flex-col gap-4 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 dark:bg-zinc-900/30"
                >
                    <ExternalCommentSection
                        :source="item.preferred_source"
                        :external_id="item.external_id"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
