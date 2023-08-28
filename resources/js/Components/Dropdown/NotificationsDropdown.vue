<script setup>
import Dropdown from '@/Components/Dropdown/Dropdown.vue';

import BellIcon from '~/images/icons/bell.svg';
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import {onMounted, ref} from "vue";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";
import NotificationCard from "@/Components/Cards/NotificationCard.vue";

const notifications = ref([]);
const loaded = ref(false);

onMounted(() => {
    axios.get(route('api.feed.subscriptions')).then((response) => {
        notifications.value = notifications.value.concat(response.data.streams.data);
        notifications.value = notifications.value.concat(response.data.videos.data);
        loaded.value = true;
    });
});
</script>


<template>
    <div class="relative  flex">
        <Dropdown align="right" width="96" distance="1.5">
            <template #trigger>
                <span class="inline-flex rounded-md  ">
                    <button
                        type="button"
                        class=" inline-flex items-center h-full  transition ease-in-out duration-150"
                    >
                        <BellIcon class="w-6 aspect-square rounded-full text-white aspect-square  "/>
                    </button>
                </span>
            </template>

            <template #content>
                <div class="flex flex-col gap-y-1 block w-full px-2 py-1 text-left text-sm">

                    <div class="flex justify-between pb-4 pt-2 px-4 text dark:textDark w-full">
                        <span class="font-bold text-xl  ">Notifications</span>
                        <Link :href="route('profile.edit')">
                            <font-awesome-icon :icon="['fas', 'gear']" class="h-6 w-6 "/>
                        </Link>
                    </div>

                    <hr class="pointer-events-none flex-grow border-1 border-zinc-300 dark:border-zinc-700 rounded-full" />

                    <div class="flex flex-col gap-y-1 block w-full px-2 py-1 text-left text-sm ">
                        <NotificationCard v-for="item in notifications" :item="item" :key="item.id"/>
                    </div>



                    <div class="mt-20" v-if="loaded && notifications.length === 0">
                        <ErrorMessage :message="'Whoops you have no new content'"/>
                    </div>
                </div>
            </template>
        </Dropdown>
    </div>

</template>

