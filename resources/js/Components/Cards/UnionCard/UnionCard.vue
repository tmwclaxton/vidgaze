<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useToastStore} from "@/Stores/ToastStore";

const name = 'UnionCard';
const props = defineProps({
    union: {
        type: Object,
        required: true
    },
    userUnionIds: {
        type: Array,
        required: true
    }
});

function joinUnion(unionId) {
    axios.post(route('api.union.join', {
        union_id: unionId
    }))
        .then(response => {
            useToastStore().add({
                message: response.data.message,
                type: response.data.toastType
            })
            props.userUnionIds.push(unionId);
        })
        .catch(error => {
            console.log(error);
        });
}

function leaveUnion(unionId) {
    axios.post(route('api.union.leave', {
        union_id: unionId
    }))
        .then(response => {
            useToastStore().add({
                message: response.data.message,
                type: response.data.toastType
            })
            props.userUnionIds.splice(props.userUnionIds.indexOf(unionId), 1);
        })
        .catch(error => {
            console.log(error);
        });
}

</script>
<template>
    <ConsistentContentHolder>

        <div class="p-5 ">
            <a href="#" class="">
                <h5 class="w-full text-2xl font-bold tracking-tight" v-text="union.name"></h5>
                <p class=" text-sm font-bold" v-text="union.members_count"/>
            </a>
            <p class="mt-2 mb-5 font-normal " v-text="union.description"></p>
            <div class="w-full flex h-10 ">
                <quaternary-button class="w-full mr-2"  @click="joinUnion(union.id)" v-if="!userUnionIds.includes(union.id)">
                    <font-awesome-icon icon="plus" class="mr-2"/>
                    Join
                </quaternary-button>
                <quaternary-button class="w-full mr-2"  @click="leaveUnion(union.id)" v-if="userUnionIds.includes(union.id)">
                    <font-awesome-icon icon="minus" class="mr-2"/>
                    Leave
                </quaternary-button>
            </div>
        </div>
    </ConsistentContentHolder>

</template>
