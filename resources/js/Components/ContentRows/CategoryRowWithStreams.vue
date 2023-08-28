<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import {computed, onMounted, ref} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const name = 'CategoryRowWithStreams';
const streams = ref([]);
const expand = ref(false);
const expandedStreams = computed(() => {
    if (expand.value) {
        return streams.value;
    } else {
        return streams.value.slice(0, 6);
    }
});


const props = defineProps({
    category: {
        type: Object,
        required: true
    }
});


const fetchStreams = async () => {
    await useContentRoutesStore().getStreams(12, props.category.id, 0)
        .then(response => {
            setTimeout(() => {
                streams.value = response;
            }, 500); // 500ms delay
        })
};

onMounted(async () => {
    await fetchStreams();
});
</script>


<template>

        <div v-if="expandedStreams.length !== 0">
            <div class="flex flex-row gap-2  my-4 mb-8 ">
                <!--<StreamIcon class="w-6 h-6 my-auto"/>-->
                <font-awesome-icon class="w-6 h-6 my-auto" :icon="['fas', 'gamepad']" />

                <Link :href="route('category.show',{slug:category.slug})"  class="font-bold text-2xl select-none" v-text="category.name"/>
            </div>

            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(stream, index) in expandedStreams" :key="stream.id">
                    <VideoStreamCard :item="stream" :category_page="true"/>
                </template>
                <!--<template v-if="streams.length === 0" v-for="i in 6">-->
                <!--    <VideoStreamSkeleton />-->
                <!--</template>-->
            </div>

            <RowDivider text="Show more" v-if="!expand" @click="expand = true">
                <font-awesome-icon class="w-6 h-6 my-auto" :icon="['fas', 'caret-down']" />
            </RowDivider>
            <RowDivider   v-if="expand" @click="expand = false">
                <!--<font-awesome-icon class="w-6 h-6 my-auto" :icon="['fas', 'caret-up']" />-->
            </RowDivider>
        </div>
</template>

