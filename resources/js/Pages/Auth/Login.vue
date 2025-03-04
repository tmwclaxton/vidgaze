

<script setup>
import Checkbox from '@/Components/Inputs/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import GoogleButton from '@/Components/Buttons/GoogleButton.vue';
import AppleButton from '@/Components/Buttons/AppleButton.vue';

import TextInput from '@/Components/Inputs/TextInput.vue';
import {Head, Link, router, useForm} from '@inertiajs/vue3';
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import { useAuthStore } from "@/Stores/AuthStore";
const authStore = useAuthStore();
import {reactive, ref, watch} from "vue";

const form = reactive({
    data: {
        email: ref(''),
        password: ref(''),
        remember: ref(false),
        errors: {},
        processing: false,
    },
    errors: {},
});

const submit = async () => {
    authStore.login(form.data).then(() => {
        if (localStorage.getItem('intended') ) {
            router.visit(localStorage.getItem('intended'));
            localStorage.removeItem('intended');
        } else if (localStorage.getItem('token')) {
            router.visit(route('home'));
        }
    }).catch(function (error) {
        const errors = (error.response.data.errors);
        form.errors.email = errors.email ? errors.email[0] : null;
        form.errors.password = errors.password ? errors.password[0] : null;
    });
};


</script>
<template>
    <GuestLayout>
        <Head title="Log in" />



        <p class="font-bold text-xl text dark:textDark mb-6">Welcome back to VidGaze</p>

        <!--<div class="flex flex-row justify-between w-full px-1">-->
        <!--    <GoogleButton >-->
        <!--        Sign in with Google-->
        <!--    </GoogleButton>-->
        <!--    <AppleButton >-->
        <!--        Sign in with Apple-->
        <!--    </AppleButton>-->
        <!--</div>-->
        <!--<HorizontalLineText text="or" class=""/>-->

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Your Email" class=""/>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.data.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class=""/>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.data.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex flex-row justify-between mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.data.remember" />
                    <span class="ml-2 text-sm text-zinc-600 dark:text-zinc-400">Remember me</span>
                </label>
                <Link
                    :href="route('password.request')"
                    class="font-semibold text-sm text-blue-600 dark:text-blue-400 hover:text-zinc-900 dark:hover:text-zinc-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800"
                >
                    Forgot your password?
                </Link>
            </div>

            <div class="flex items-center justify-end mt-4 mb-2">

                <PrimaryButton class=" w-full rounded-full text-center px-auto dark:bg-zinc-900 hover:dark:bg-zinc-900/70" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    <p class="text-center w-full capitalize text-sm dark:textDark">Log in</p>
                </PrimaryButton>

            </div>
            <span class="flex-grow text-left text-sm pr-4 text-zinc-600 dark:text-zinc-400  ">
                                Don't have an account yet?
                            <Link class="font-bold text-blue-700  dark:text-blue-400 hover:underline"
                               :href="route('register')">
                                Sign Up
                            </Link>
             </span>
        </form>
    </GuestLayout>
</template>
