<script setup>
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import Title from "@/Components/General/Title.vue";
import HistoryIcon from '~/images/icons/subscriptions.svg';
import {ref} from "vue";

const name = "PlaylistBar";

const props = defineProps({
    text: {
        type: String,
        required: true
    },
});

</script>
<template>
    <div class="flex flex-row w-full justify-between ">
        <Title :text="props.text" class="my-auto">
            <HistoryIcon class="w-5"/>
        </Title>
        <QuaternaryButton class="h-max" @click="">
            <span class="font-semibold">See all</span>
        </QuaternaryButton>
    </div>

    <row-divider class="mt-6 mb-3 rounded-2xl"></row-divider>

    <div class="mx-1  mb-5 flex grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 ml:grid-cols-4 4xl:grid-cols-6 gap-4">

    </div>

</template>

