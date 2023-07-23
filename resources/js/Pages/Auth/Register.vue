

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import { Head, Link, useForm } from '@inertiajs/vue3';
import GoogleButton from "@/Components/Buttons/GoogleButton.vue";
import AppleButton from "@/Components/Buttons/AppleButton.vue";

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <p class="font-bold text-xl text dark:textDark mb-6">Create your VidGaze Account</p>

        <div class="flex flex-row justify-between w-full px-1">
            <GoogleButton >
                Sign up with Google
            </GoogleButton>
            <AppleButton >
                Sign up with Apple
            </AppleButton>

        </div>


        <HorizontalLineText text="or" class="select-none"/>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="username" value="Channel Name" />

                <TextInput
                    id="username"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.username"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
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

                <InputError class="mt-2" :message="form.errors.password" />
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

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4  text-sm ">
                <div class="flex items-center">
                    <label class="flex items-center">
                         <Checkbox v-model:checked="form.terms" name="terms" id="terms"/>
                        <span class="ml-2 text-sm text-zinc-600 dark:text-zinc-400 select-none">
                            I agree to the
                            <Link :href="route('terms')" class="font-bold">Terms of Service</Link>
                            and
                            <Link :href="route('privacy')" class="font-bold">Privacy Policy</Link>
                        </span>
                    </label>


                </div>

                <InputError class="mt-2" :message="form.errors.terms" />

            </div>

            <div class="flex items-center justify-end mt-4 mb-2">

                <PrimaryButton class=" w-full rounded-full text-center px-auto dark:bg-zinc-900 hover:dark:bg-zinc-900/70" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    <p class="text-center w-full capitalize text-sm dark:textDark">Create an account</p>
                </PrimaryButton>

            </div>

            <span class="flex-grow text-left text-sm pr-4  text-zinc-600 dark:text-zinc-400 ">
                                Already have an account?
                            <Link class="font-bold text-blue-700  dark:text-blue-400 hover:underline"
                               :href="route('login')">
                                Log in
                            </Link>
             </span>
        </form>
    </GuestLayout>
</template>
