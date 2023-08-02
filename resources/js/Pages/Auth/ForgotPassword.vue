<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {reactive, ref} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
const authStore = useAuthStore();
defineProps({
    status: String,
});

const form = reactive({
    email: ref(''),
    processing: false,
});

const errors = reactive({
    email: ref(''),
});
const submit = () => {
    form.processing = true;
    authStore.forgotPassword(form).then(() => {
        form.processing = false;
    }).catch(function (error) {
        const suppliedErrors = (error.response.data.errors);
        errors.email = suppliedErrors.email ? suppliedErrors.email[0] : null;
    });

};
</script>
<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset
            link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
