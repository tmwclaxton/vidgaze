<script setup>
import { computed, reactive, ref } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/Stores/ToastStore';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useAuthStore } from '@/Stores/AuthStore';
const authStore = useAuthStore();
const props = defineProps({
    status: String,
});

const form = reactive({ processing: false });
const linkSent = ref(false);

// The API answers with JSON and authenticates with the bearer token, so call it with
// axios rather than an Inertia form post.
const submit = () => {
    form.processing = true;
    axios.post(route('api.verification.send')).then(response => {
        linkSent.value = true;
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType === 'success' ? 'success' : 'normal',
        });
    }).catch(error => {
        useToastStore().add({
            message: error.response?.data?.message ?? 'Could not send the verification email, please try again.',
            type: 'warning',
        });
    }).finally(() => {
        form.processing = false;
    });
};

const verificationLinkSent = computed(() => linkSent.value || props.status === 'verification-link-sent');
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,

};
</script>
<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link
            we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400" v-if="verificationLinkSent">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    @click="authStore.logout()"
                    as="button"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >Log Out</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
