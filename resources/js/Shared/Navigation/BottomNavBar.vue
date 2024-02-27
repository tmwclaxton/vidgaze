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
    <div id="bottomNav" class="bottom-0 z-50 sticky sm:hidden bg-vidgaze-blue-nav pointer-events-auto backdrop-blur-sm " >
        <div class="max-w- screen-2xl mx-auto pl-5 sm:pl-8 pr-4 sm:pr-6 lg:pr-8  flex flex-col">
            <div class="h-0.5"></div>
            <div class="flex justify-between h-16 ">
                <div class="flex w-full text-white flex-row justify-between items-center">
                    <Link :href="route('home')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <font-awesome-icon :icon="['fas', 'video']" class="w-5 h-5 mx-auto"/>
                        <p class="text-sm text-center font-bold">Videos</p>
                    </Link>
                    <Link :href="route('streams.index')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <StreamIcon class="w-5 h-5 mx-auto"/>
                        <p class="  text-sm text-center font-bold">Streams</p>
                    </Link>
                    <!--<Link :href="route('videos.shorts')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">-->
                    <!--    <font-awesome-icon :icon="['fas', 'fire']" class="w-5 h-5 mx-auto"/>-->
                    <!--    <p class="  text-sm text-center font-bold">Shorts</p>-->
                    <!--</Link>-->
                    <Link v-if="authStore.user"
                        :href="route('feed.subscriptions')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <SubscriptionsIcon class="w-5 h-5 mx-auto"/>
                        <p class="  text-sm text-center font-bold">Subscriptions</p>
                    </Link>
                    <Link  v-if="authStore.user"
                           :href="route('feed.library')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <LibraryIcon class="w-5 h-5 mx-auto"/>
                        <p class="  text-sm text-center font-bold">Library</p>
                    </Link>
                    <Link v-if="!authStore.user"
                        :href="route('login')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="w-5 h-5 mx-auto"/>
                        <p class="  text-sm text-center font-bold">Login</p>
                    </Link>
                    <Link v-if="!authStore.user"
                        :href="route('register')" class="flex flex-col flex-grow justify-between justify-items-center align-middle ">
                        <font-awesome-icon :icon="['fas', 'user-plus']" class="w-5 h-5 mx-auto"/>
                        <p class="  text-sm text-center font-bold">Register</p>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>



