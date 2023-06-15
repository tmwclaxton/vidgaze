<template>
    <div class="flex flex-row gap-2 my-4 mb-8">
        <font-awesome-icon :icon="['fass', 'microphone']" class="w-6 h-6 my-auto" />
        <p class="font-bold text-2xl select-none">VidGaze Podcasters</p>
    </div>

    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-3 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template v-for="creator in creators" :key="creator.id">
            <PodcastCreatorCard :creator="creator" />
        </template>
        <template v-if="creators.length === 0" v-for="i in 6" :key="i">
            <PodcastCreatorSkeleton />
        </template>
    </div>

    <RowDivider />
</template>

<script setup>
import PodcastCreatorCard from "@/Components/Cards/PodcastCards/PocastCreatorCard/PodcastCreatorCard.vue";
import PodcastCreatorSkeleton from "@/Components/Cards/PodcastCards/PocastCreatorCard/PodcastCreatorSkeleton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import { ref, onMounted } from "vue";
import axios from 'axios';

const name = 'TopPodcastersRow';

const creators = ref([]);

const fetchCreators = async () => {
    axios.get(route('creator.infinite'),  { params: { podcasters: true, perPage: 6  } } )
        .then(response => {
            setTimeout(() => {
                creators.value = response.data.data;
            }, 300); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
}

onMounted(() => {
    fetchCreators();
})
</script>
