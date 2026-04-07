<script setup>
import ProfileDropdown from "@/Components/Dropdown/ProfileDropdown.vue";
import UploadDropdown from "@/Components/Dropdown/UploadDropdown.vue";
import NotificationsDropdown from "@/Components/Dropdown/NotificationsDropdown.vue";
import TopNavigationLinks from '@/Shared/Navigation/Partials/TopNavigationLinks.vue';
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';

import {Link, router} from "@inertiajs/vue3";
import Searchbar from "@/Shared/Navigation/Partials/Searchbar.vue";
import TopNavButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();
import {useAuthStore} from "@/Stores/AuthStore";
import StreamIcon from '~/images/icons/livestreams.svg';
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import LibraryIcon from '~/images/icons/library.svg';


const authStore = useAuthStore();

//name of the component
const name = 'BottomNavBar';


</script>

<template>

    <!--Bottom Nav Bar-->
    <div id="bottomNav" class="bottom-0 z-10 sticky sm:hidden bg-vidgaze-blue-nav pointer-events-auto backdrop-blur-sm " >
        <div class="max-w- screen-2xl mx-auto pl-5 sm:pl-8 pr-4 sm:pr-6 lg:pr-8  flex flex-col">
            <div class="h-0.5"></div>
            <div class="flex justify-between h-16 ">
                <div class="flex w-full flex-row items-center justify-between text-white">
                    <Link
                        :href="route('home')"
                        class="group flex flex-grow flex-col items-center justify-between align-middle"
                    >
                        <font-awesome-icon
                            :icon="['fas', 'video']"
                            class="mx-auto h-5 w-5 text-sky-400 drop-shadow-[0_0_8px_rgba(56,189,248,0.45)] transition-transform duration-200 group-hover:scale-110"
                        />
                        <p
                            class="bg-gradient-to-r from-sky-300 to-cyan-400 bg-clip-text text-center text-sm font-bold text-transparent"
                        >
                            Videos
                        </p>
                    </Link>
<!--                    <Link :href="route('streams.index')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">-->
<!--                        <StreamIcon class="w-5 h-5 mx-auto"/>-->
<!--                        <p class="  text-sm text-center font-bold">Streams</p>-->
<!--                    </Link>-->
                    <!--<Link :href="route('videos.shorts')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">-->
                    <!--    <font-awesome-icon :icon="['fas', 'fire']" class="w-5 h-5 mx-auto"/>-->
                    <!--    <p class="  text-sm text-center font-bold">Shorts</p>-->
                    <!--</Link>-->
                    <Link
                        v-if="authStore.user"
                        :href="route('feed.subscriptions')"
                        class="group flex flex-grow flex-col items-center justify-between align-middle"
                    >
                        <SubscriptionsIcon
                            class="mx-auto h-5 w-5 fill-current text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.45)] transition-transform duration-200 group-hover:scale-110"
                        />
                        <p
                            class="bg-gradient-to-r from-indigo-300 to-blue-400 bg-clip-text text-center text-sm font-bold text-transparent"
                        >
                            Subscriptions
                        </p>
                    </Link>
                    <Link
                        v-if="authStore.user"
                        :href="route('feed.library')"
                        class="group flex flex-grow flex-col items-center justify-between align-middle"
                    >
                        <LibraryIcon
                            class="mx-auto h-5 w-5 fill-current text-emerald-400 drop-shadow-[0_0_8px_rgba(52,211,153,0.45)] transition-transform duration-200 group-hover:scale-110"
                        />
                        <p
                            class="bg-gradient-to-r from-emerald-300 to-teal-400 bg-clip-text text-center text-sm font-bold text-transparent"
                        >
                            Library
                        </p>
                    </Link>
                    <Link
                        v-if="!authStore.user"
                        :href="route('login')"
                        class="group flex flex-grow flex-col items-center justify-between align-middle"
                    >
                        <font-awesome-icon
                            :icon="['fas', 'sign-in-alt']"
                            class="mx-auto h-5 w-5 text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.45)] transition-transform duration-200 group-hover:scale-110"
                        />
                        <p
                            class="bg-gradient-to-r from-cyan-300 to-teal-400 bg-clip-text text-center text-sm font-bold text-transparent"
                        >
                            Login
                        </p>
                    </Link>
                    <Link
                        v-if="!authStore.user"
                        :href="route('register')"
                        class="group flex flex-grow flex-col items-center justify-between align-middle"
                    >
                        <font-awesome-icon
                            :icon="['fas', 'user-plus']"
                            class="mx-auto h-5 w-5 text-fuchsia-400 drop-shadow-[0_0_8px_rgba(232,121,249,0.45)] transition-transform duration-200 group-hover:scale-110"
                        />
                        <p
                            class="bg-gradient-to-r from-violet-300 to-fuchsia-400 bg-clip-text text-center text-sm font-bold text-transparent"
                        >
                            Register
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>



