<script setup>

import {onMounted, ref} from "vue";
import CommentSection from "@/Components/CommentSection/CommentSection.vue";

const chatrooms = ref([]);
const currentChatroom = ref(null);

const grabChatrooms = async () => {
    const response = await axios.get(route('api.chatroom.index'))
        .then(response => {
            chatrooms.value = response.data.chatrooms;
            currentChatroom.value = response.data.chatrooms[0];
        }).catch(error => {
            console.log(error);
        });
};

onMounted(() => {
    grabChatrooms();


    // CommentSectionStore.item = ""
});

const keyUpdator = ref(0);
const changeChatroom = (chatroom) => {
    currentChatroom.value = chatroom;
    keyUpdator.value++;
};


</script>

<template>
    <div class="p-2">
        <Head title="Global Chat" />


        <div class="flex flex-row gap-x-5 h-screen justify-start items-start">
            <div class="flex flex-col flex-wrap justify-center">
                <div class="w-48 lg:w-72 p-0.5 cursor-pointer" v-for="chatroom in chatrooms" :key="chatroom.id" @click="changeChatroom(chatroom)">
                    <div class="shadow-md rounded-md p-4 bg-white dark:bg-zinc-800">
                        <h2 class="text-md font-bold">{{ chatroom.name }}</h2>
                        <p class="text-sm hidden lg:flex">{{ chatroom.description }}</p>
                    </div>
                </div>
            </div>

            <div class="border-l-2 border-gray-200 dark:border-gray-800 h-full"></div>


            <div class="flex flex-col gap-y-5 w-full" v-if="currentChatroom !== null">
                <div class="flex flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold" v-text="currentChatroom.name"></h1>
                </div>
                <CommentSection  :item="currentChatroom" :key="keyUpdator" :default-category="'new'" />

            </div>
        </div>
    </div>
</template>
