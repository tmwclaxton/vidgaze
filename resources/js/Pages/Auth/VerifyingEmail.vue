<script setup>
import {onMounted} from "vue";
import {useToastStore} from "@/Stores/ToastStore";
import {router} from "@inertiajs/vue3";

const name = 'VerifyingEmail';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    hash: {
        type: String,
        required: true,
    },
});

onMounted(() => {
    axios.get(route('api.verification.email.confirm', {id: props.id, hash: props.hash})).then(() => {
        useToastStore().add({
            message: 'Your email has been verified.',
            type: 'success',
        });
        // redirect to home
        router.visit(route('home'));

    });
});

</script>

<template>
    <SeoHead title="Verifying email" description="Confirming your email address for VidGaze." noindex />
</template>

