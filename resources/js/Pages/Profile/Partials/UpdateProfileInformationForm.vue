<script setup>
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {useAuthStore} from "@/Stores/AuthStore";
import {useToastStore} from "@/Stores/ToastStore";
import {reactive, ref} from "vue";


const props = defineProps({
    mustVerifyEmail: Boolean,
});

const user = useAuthStore().user;

const form = reactive({
    data: {
        first_name: user.first_name,
        last_name: user.last_name,
        dob: user.dob,
        email: user.email,
        processing: false,
    },
    errors: {},
});

const save =() => {
    form.processing = true;

    axios.patch(route('api.profile.update',{
        first_name: form.data.first_name,
        last_name: form.data.last_name,
        email: form.data.email,
    })).then(() => {
        useToastStore().add({
            message: 'Profile updated.',
            type: 'success',
        });
        form.processing = false;
    });
}

const sendVerificationEmail = () => {

    axios.post(route('api.verification.send', {email: user.email})).then(() => {
        useToastStore().add({
            message: 'A new verification link has been sent to your email address.',
            type: 'success',
        });
    });
};

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <div class="mt-6 space-y-6">
            <div>
                <InputLabel for="first_name" value="First Name" />

                <TextInput
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.data.first_name"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2" :message="form.errors.first_name" />
            </div>
            <div>
                <InputLabel for="last_name" value="Last Name" />

                <TextInput
                    id="last_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.data.last_name"
                    required
                    autofocus
                    autocomplete="family-name"
                />

                <InputError class="mt-2" :message="form.errors.last_name" />
            </div>
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.data.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <span @click="sendVerificationEmail"
                        class="select-none cursor-pointer underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton @click="save" :disabled="form.data.processing">Save</PrimaryButton>

                <!--<Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">-->
                <!--    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>-->
                <!--</Transition>-->
            </div>
        </div>
    </section>
</template>
