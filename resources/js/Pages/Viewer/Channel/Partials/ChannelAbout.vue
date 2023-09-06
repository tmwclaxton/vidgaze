<script setup>
const name = 'ChannelHome';
const props = defineProps({
    channel: {
        type: Object
    }
});
</script>
<template>
    <span class="flex flex-col gap-y-5">
        <span class="col-span-1" v-if="channel.bio">
            <p class="text-base font-bold mb-2">Biography</p>
            <p class="px-2" v-text="channel.bio"></p>
        </span>
        <span class="col-span-1" v-if="channel.contact_email">
            <p class="text-base font-bold mb-2">Contact Email</p>
            <p class="px-2" v-text="channel.contact_email"></p>
        </span>
        <span class="col-span-1 ">
            <p class="text-base font-bold mb-2">Stats</p>
            <p class="px-2" v-text="'Joined ' + channel.created_at"></p>
        </span>
    </span>
</template>
