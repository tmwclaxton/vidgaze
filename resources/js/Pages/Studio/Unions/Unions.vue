<script setup>
import TitleComponent from "@/Components/General/TitleComponent.vue";

import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {onMounted, ref, watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import UnionCard from "@/Components/Cards/UnionCard/UnionCard.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const unions = ref([]);
const userUnionIds = ref([]);

onMounted(async () => {
    if (useAuthStore().user) {
        await getUnions();
    } else {
        watch(() => useAuthStore().user, async () => {
            await getUnions();
        });
    }
});

async function getUnions() {
    await axios.get(route('api.union.index'))
        .then(response => {
            unions.value = response.data.unions;
            userUnionIds.value = response.data.userUnionIds;
        })
        .catch(error => {
            console.log(error);
        });
}

</script>

<template>
    <Head :title="`Unions`" />
    <ConsistentPadding>
            <div class=" mx-auto">
                <div class="flex flex-row gap-x-2">
                    <font-awesome-icon icon="users" class="mr-2 my-auto h-8"/>
                    <p class=" font-bold text-4xl  m-0">Join a union today!</p>
                </div>
                <p class="my-2  mb-6  text-sm   text-justify">
                    By being a part of a union, you give that union the power to change the primary source of your
                    videos and/or streams and also the ability to prevent you from uploading/streaming to certain
                    platforms via VidGaze during a boycott.
                    &nbspDon't worry you can still upload directly using the boycotted platform or by leaving the
                    union... &nbsp But stand firm and don't cross the picket line!
                </p>

            </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 gap-4 mt-5 pb-10 ">
            <UnionCard  v-for="union in unions" :union="union" :key="union.id" :userUnionIds="userUnionIds"/>
        </div>
    </ConsistentPadding>
</template>
