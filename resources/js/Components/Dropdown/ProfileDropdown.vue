<script setup>
import DropdownLink from '@/Components/Links/DropdownLink.vue';
import Dropdown from '@/Components/Dropdown/Dropdown.vue';
import {Link} from '@inertiajs/vue3';

import CoinsIcon from '~/images/icons/coins.svg';
import StudioIcon from '~/images/icons/light.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import LogoutIcon from '~/images/icons/logout.svg';

import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useAuthStore} from "@/Stores/AuthStore";
const authStore = useAuthStore();
</script>


<template>


    <div class="relative flex" v-if="authStore.user.creator != null">
        <Dropdown align="right" width="64" distance="1.5"  >
            <template #trigger>
                <span class="inline-flex rounded-md   ">
                    <button
                        type="button"
                        class="inline-flex items-center h-full border border-transparent  rounded-md bg-transparent hover:text-zinc-300 focus:outline-none transition ease-in-out duration-150"
                    >
                        <!--<img class="w-full   "-->
                        <!--     v-bind:src="authStore.user.creator.banner_url">-->
                        <img class="h-8 aspect-square rounded-full bg-zinc-800 aspect-square  "
                             v-bind:src="authStore.user.creator.avatar_url">

                    </button>
                </span>
            </template>

            <template #content >
                <div class="p-2">
                    <div class="flex flex-row space-x-2 block w-full px-4 py-2 pb-4 text-left text-sm  ">
                        <!--Profile picture-->
                        <img class="h-10 aspect-square rounded-full bg-zinc-800 aspect-square  "
                             v-bind:src="authStore.user.creator.avatar_url">
                        <div class="flex flex-col">
                            <span class="text dark:text-white font-bold">{{ authStore.user.email }}</span>
                            <Link :href="route('profile.edit')" class="text-blue-500 font-bold">
                                Manage your account
                            </Link>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-800"></div>

                    <DropdownLink v-if="authStore.admin" :href="route('admin.dashboard')" class="flex flex-row space-x-2">
                        <font-awesome-icon :icon="['fas', 'helmet-safety']" class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Admin Dashboard</span>
                    </DropdownLink>
                    <DropdownLink :href="route('channel.show', authStore.user.creator.slug)"
                                  class="flex flex-row space-x-2">
                        <ProfileIcon class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Your Channel</span>
                    </DropdownLink>
                    <DropdownLink :href="route('studio.dashboard')" class="flex flex-row space-x-2">
                        <StudioIcon class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">VidGaze Studio</span>
                    </DropdownLink>
                    <DropdownLink :href="route('marketplace')" class="flex flex-row space-x-2">
                        <CoinsIcon class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Buy VidCoins</span>

                    </DropdownLink>
                    <DropdownLink
                        :span="true"
                        class="flex flex-row gap-x-2" as="button" @click="authStore.logout()" >
                        <LogoutIcon class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Log Out</span>
                    </DropdownLink>
                </div>

            </template>
        </Dropdown>
    </div>
</template>

