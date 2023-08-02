<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import {Head, router, useForm} from '@inertiajs/vue3';
import {reactive, ref} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";

const props = defineProps({
    email: String,
    token: String,
});



const form = reactive({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
    remember: ref(false), // this is so we can forward this form to the login function
    processing: false,
});

const errors = reactive({
    email: '',
    password: '',
    token: '',
});

const submit = () => {
    useAuthStore().resetPassword(form).then(() => {

    }).catch(function (error) {
        if (error.response === undefined) {
            return;
        }
        const suppliedErrors = (error.response.data.errors);
        errors.email = suppliedErrors.email ? suppliedErrors.email[0] : null;
        errors.password = suppliedErrors.password ? suppliedErrors.password[0] : null;
        errors.token = suppliedErrors.token ? suppliedErrors.token[0] : null;
    });

};
</script>
<template>
    <GuestLayout>
        <Head title="Reset Password" />

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

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
