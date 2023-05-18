
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout
};
</script>

<script setup>
import { Head } from '@inertiajs/vue3';
import ShortsPlayer from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayer.vue";
import {onMounted, ref} from "vue";
import ShortsPlayerSkeleton from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayerSkeleton.vue";


const name = 'Shorts'

const shorts = ref([]);
const category = ref('popular');
const fetchShorts = async () => {
    const shortsIds = shorts.value.map(short => short.id).join(',');
    axios.get(route('videos.infinite'),  {
        params: {
            category: category.value,
            shorts: true,
            perPage: 8,
            videoIds: shortsIds
        }
    }).then(response => {
            setTimeout(() => {
                shorts.value = response.data.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

onMounted(async () => {
    await fetchShorts();
});

</script><template>
    <Head title="VidGaze Shorts" />

    <div id="shortsScrollArea" class="max-h-[calc(100vh-4rem)] w-full duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out">
        <ShortsPlayer v-for="short in shorts" :video="short"/>
        <ShortsPlayerSkeleton v-if="shorts.length === 0" v-for="n in 4"  />
    </div>
</template>
