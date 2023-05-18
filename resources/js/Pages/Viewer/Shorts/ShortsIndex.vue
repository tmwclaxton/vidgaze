
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
import { useInfiniteScroll, useVirtualList } from '@vueuse/core'

const name = 'Shorts'

const shorts = ref([]);

const { list, containerProps, wrapperProps } = useVirtualList(shorts, {
    itemHeight: window.innerHeight - 64,
    itemWidth: 400,
});


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
                shorts.value = shorts.value.concat(response.data.data);
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

onMounted(async () => {
    await fetchShorts();
});

useInfiniteScroll(
    containerProps.ref,
    async () => {
        await fetchShorts();
    },
    {
        distance: 2 * (window.innerHeight - 64), // load more when scrolled to within 2 shorts from the bottom
    }
)

</script><template>
    <Head title="VidGaze Shorts" />
<div v-bind="containerProps" class="max-h-[calc(100vh-4rem)] duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out">
    <div v-bind="wrapperProps">
        <div id="shortsScrollArea" class=" w-full ">
            <template v-for="{index,data} in list" :key="index">
                <ShortsPlayer :video="data"/>
            </template>
            <ShortsPlayerSkeleton  v-for="n in 1"  />
        </div>
    </div>
</div>
</template>
