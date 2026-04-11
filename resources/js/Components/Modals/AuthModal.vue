<script setup>
import Checkbox from '@/Components/Inputs/Checkbox.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useAuthModalStore } from '@/Stores/AuthModalStore';
import { useAuthStore } from '@/Stores/AuthStore';
import { computed, reactive, ref, watch } from 'vue';

const authModalStore = useAuthModalStore();
const authStore = useAuthStore();

const loginForm = reactive({
    email: '',
    password: '',
    remember: false,
    errors: {},
});

const registerForm = reactive({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
    errors: {},
});

const forgotForm = reactive({
    email: '',
    errors: {},
});

const loginProcessing = ref(false);
const registerProcessing = ref(false);
const forgotProcessing = ref(false);
const forgotStatusMessage = ref('');

const titleId = computed(() => {
    if (authModalStore.panel === 'forgot_password') return 'auth-modal-forgot-title';
    if (authModalStore.panel === 'register') return 'auth-modal-register-title';
    return 'auth-modal-login-title';
});

watch(
    () => authModalStore.show,
    (open) => {
        if (open) {
            loginForm.errors = {};
            registerForm.errors = {};
            forgotForm.errors = {};
            forgotStatusMessage.value = '';
        }
    }
);

function close() {
    authModalStore.close();
}

function goToForgot() {
    forgotForm.email = loginForm.email || '';
    forgotStatusMessage.value = '';
    forgotForm.errors = {};
    authModalStore.setPanel('forgot_password');
}

function goToLogin() {
    forgotStatusMessage.value = '';
    forgotForm.errors = {};
    authModalStore.setPanel('login');
}

async function submitLogin() {
    loginForm.errors = {};
    loginProcessing.value = true;
    try {
        await authStore.login({
            email: loginForm.email,
            password: loginForm.password,
            remember: loginForm.remember,
        });
        await authModalStore.resolvePendingAfterAuth();
    } catch (error) {
        const errs = error.response?.data?.errors;
        if (errs) {
            loginForm.errors.email = errs.email?.[0] ?? null;
            loginForm.errors.password = errs.password?.[0] ?? null;
        }
    } finally {
        loginProcessing.value = false;
    }
}

async function submitRegister() {
    registerForm.errors = {};
    registerProcessing.value = true;
    try {
        await authStore.register({
            username: registerForm.username,
            email: registerForm.email,
            password: registerForm.password,
            password_confirmation: registerForm.password_confirmation,
            terms: registerForm.terms,
        });
        await authModalStore.resolvePendingAfterAuth();
    } catch (error) {
        const errs = error.response?.data?.errors;
        if (errs) {
            registerForm.errors.email = errs.email?.[0] ?? null;
            registerForm.errors.password = errs.password?.[0] ?? null;
            registerForm.errors.username = errs.username?.[0] ?? null;
            registerForm.errors.terms = errs.terms?.[0] ?? null;
            registerForm.errors.password_confirmation = errs.password_confirmation?.[0] ?? null;
        }
    } finally {
        registerProcessing.value = false;
    }
}

async function submitForgot() {
    forgotForm.errors = {};
    forgotStatusMessage.value = '';
    forgotProcessing.value = true;
    try {
        const message = await authStore.forgotPassword(forgotForm, { silent: true });
        forgotStatusMessage.value = message || 'We have emailed your password reset link.';
    } catch (error) {
        const errs = error.response?.data?.errors;
        if (errs) {
            forgotForm.errors.email = errs.email?.[0] ?? null;
        }
    } finally {
        forgotProcessing.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="authModalStore.show"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-vidgaze-blue/40 p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
            >
                <div
                    class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-b from-slate-900/85 via-slate-950/80 to-black/90 backdrop-blur-[2px]"
                    aria-hidden="true"
                />
                <!-- subtle stars like guest layout -->
                <div
                    class="pointer-events-none absolute inset-0 z-0 opacity-[0.18]"
                    aria-hidden="true"
                    style="
                        background-image: url('/images/logos/vidgaze/night_sky2.jpg');
                        background-size: cover;
                        background-position: center;
                    "
                />

                <div
                    class="absolute inset-0 z-[1] bg-black/50 backdrop-blur-sm"
                    aria-hidden="true"
                    @click="close"
                />

                <div
                    class="relative z-10 flex max-h-[min(92vh,760px)] w-full max-w-md flex-col overflow-hidden rounded-lg border border-zinc-200/90 bg-white shadow-md dark:border-white/[0.08] dark:bg-vidgaze-blue-nav dark:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.65)]"
                    @click.stop
                >
                    <div
                        class="h-1 w-full shrink-0 bg-gradient-to-r from-cyan-400/90 via-fuchsia-400/80 to-violet-500/85"
                        aria-hidden="true"
                    />

                    <button
                        type="button"
                        class="absolute right-2 top-3 z-20 rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white"
                        @click="close"
                    >
                        <FontAwesomeIcon :icon="['fas', 'xmark']" class="h-5 w-5" />
                        <span class="sr-only">Close</span>
                    </button>

                    <div class="overflow-y-auto px-6 py-6 sm:px-7 sm:py-6">
                        <div class="mb-5 flex justify-center">
                            <img
                                src="/images/logos/vidgaze/vidgaze_banner.png"
                                alt="VidGaze"
                                class="h-9 w-auto select-none opacity-95 sm:h-10"
                            />
                        </div>

                        <template v-if="authModalStore.panel !== 'forgot_password'">
                            <div
                                class="mb-6 flex gap-1.5 rounded-full bg-zinc-100 p-1 dark:bg-black/25 dark:ring-1 dark:ring-white/[0.06]"
                            >
                                <button
                                    type="button"
                                    class="flex-1 rounded-full py-2.5 text-center text-sm font-semibold transition duration-200"
                                    :class="
                                        authModalStore.panel === 'login'
                                            ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-200/80 dark:bg-white/10 dark:text-white dark:ring-white/10'
                                            : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100'
                                    "
                                    @click="authModalStore.setPanel('login')"
                                >
                                    Log in
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 rounded-full py-2.5 text-center text-sm font-semibold transition duration-200"
                                    :class="
                                        authModalStore.panel === 'register'
                                            ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-200/80 dark:bg-white/10 dark:text-white dark:ring-white/10'
                                            : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100'
                                    "
                                    @click="authModalStore.setPanel('register')"
                                >
                                    Sign up
                                </button>
                            </div>
                        </template>

                        <template v-else>
                            <div class="mb-5">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg py-1.5 text-sm font-semibold text-blue-600 transition hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    @click="goToLogin"
                                >
                                    <FontAwesomeIcon :icon="['fas', 'arrow-left']" class="h-3.5 w-3.5" />
                                    Back to log in
                                </button>
                            </div>
                        </template>

                        <!-- Login -->
                        <template v-if="authModalStore.panel === 'login'">
                            <p
                                id="auth-modal-login-title"
                                class="font-bold text-xl text-zinc-900 dark:textDark mb-6"
                            >
                                Welcome back to VidGaze
                            </p>
                            <form @submit.prevent="submitLogin">
                                <div>
                                    <InputLabel for="auth-modal-email" value="Your Email" class="" />
                                    <TextInput
                                        id="auth-modal-email"
                                        v-model="loginForm.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="loginForm.errors.email" />
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="auth-modal-password" value="Password" class="" />
                                    <TextInput
                                        id="auth-modal-password"
                                        v-model="loginForm.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="current-password"
                                    />
                                    <InputError class="mt-2" :message="loginForm.errors.password" />
                                </div>
                                <div class="mt-4 flex flex-row justify-between gap-2">
                                    <label class="flex min-w-0 items-center">
                                        <Checkbox v-model:checked="loginForm.remember" name="remember" />
                                        <span class="ml-2 text-sm text-zinc-600 dark:text-zinc-400">Remember me</span>
                                    </label>
                                    <button
                                        type="button"
                                        class="shrink-0 rounded-md text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-zinc-900 dark:hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800"
                                        @click="goToForgot"
                                    >
                                        Forgot your password?
                                    </button>
                                </div>
                                <div class="mt-4 mb-2 flex items-center justify-end">
                                    <PrimaryButton
                                        class="w-full rounded-full px-auto text-center dark:bg-zinc-900 hover:dark:bg-zinc-900/70"
                                        :class="{ 'opacity-25': loginProcessing }"
                                        :disabled="loginProcessing"
                                    >
                                        <p class="w-full text-center text-sm capitalize dark:textDark">Log in</p>
                                    </PrimaryButton>
                                </div>
                            </form>
                            <span class="mt-1 flex-grow text-left text-sm text-zinc-600 dark:text-zinc-400">
                                Don't have an account yet?
                                <button
                                    type="button"
                                    class="font-bold text-blue-700 hover:underline dark:text-blue-400"
                                    @click="authModalStore.setPanel('register')"
                                >
                                    Sign Up
                                </button>
                            </span>
                        </template>

                        <!-- Register -->
                        <template v-else-if="authModalStore.panel === 'register'">
                            <p
                                id="auth-modal-register-title"
                                class="font-bold text-xl text-zinc-900 dark:textDark mb-6"
                            >
                                Create your VidGaze account
                            </p>
                            <form @submit.prevent="submitRegister">
                                <div>
                                    <InputLabel for="auth-modal-username" value="Channel Name" />
                                    <TextInput
                                        id="auth-modal-username"
                                        v-model="registerForm.username"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="registerForm.errors.username" />
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="auth-modal-reg-email" value="Email" />
                                    <TextInput
                                        id="auth-modal-reg-email"
                                        v-model="registerForm.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="registerForm.errors.email" />
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="auth-modal-reg-password" value="Password" />
                                    <TextInput
                                        id="auth-modal-reg-password"
                                        v-model="registerForm.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError class="mt-2" :message="registerForm.errors.password" />
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="auth-modal-password-confirm" value="Confirm Password" />
                                    <TextInput
                                        id="auth-modal-password-confirm"
                                        v-model="registerForm.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError class="mt-2" :message="registerForm.errors.password_confirmation" />
                                </div>
                                <div class="mt-4 text-sm">
                                    <div class="flex items-center">
                                        <label class="flex items-start gap-2">
                                            <Checkbox v-model:checked="registerForm.terms" name="terms" />
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                                I agree to the
                                                <Link :href="route('terms')" class="font-bold" @click="close">
                                                    Terms of Service
                                                </Link>
                                                and
                                                <Link :href="route('privacy')" class="font-bold" @click="close">
                                                    Privacy Policy
                                                </Link>
                                            </span>
                                        </label>
                                    </div>
                                    <InputError class="mt-2" :message="registerForm.errors.terms" />
                                </div>
                                <div class="mt-4 mb-2 flex items-center justify-end">
                                    <PrimaryButton
                                        class="w-full rounded-full px-auto text-center dark:bg-zinc-900 hover:dark:bg-zinc-900/70"
                                        :class="{ 'opacity-25': registerProcessing }"
                                        :disabled="registerProcessing"
                                    >
                                        <p class="w-full text-center text-sm capitalize dark:textDark">
                                            Create an account
                                        </p>
                                    </PrimaryButton>
                                </div>
                            </form>
                            <span class="mt-1 flex-grow text-left text-sm text-zinc-600 dark:text-zinc-400">
                                Already have an account?
                                <button
                                    type="button"
                                    class="font-bold text-blue-700 hover:underline dark:text-blue-400"
                                    @click="authModalStore.setPanel('login')"
                                >
                                    Log in
                                </button>
                            </span>
                        </template>

                        <!-- Forgot password -->
                        <template v-else>
                            <p
                                id="auth-modal-forgot-title"
                                class="font-bold text-xl text-zinc-900 dark:textDark mb-4"
                            >
                                Forgot your password?
                            </p>
                            <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                                No problem. Enter your email and we will send you a reset link so you can choose a new
                                password.
                            </p>
                            <div
                                v-if="forgotStatusMessage"
                                class="mb-4 rounded-lg border border-emerald-200/80 bg-emerald-50/90 px-3 py-2.5 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                            >
                                {{ forgotStatusMessage }}
                            </div>
                            <form @submit.prevent="submitForgot">
                                <div>
                                    <InputLabel for="auth-modal-forgot-email" value="Email" />
                                    <TextInput
                                        id="auth-modal-forgot-email"
                                        v-model="forgotForm.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="forgotForm.errors.email" />
                                </div>
                                <div class="mt-4 flex items-center justify-end">
                                    <PrimaryButton
                                        class="w-full rounded-full px-auto text-center dark:bg-zinc-900 hover:dark:bg-zinc-900/70"
                                        :class="{ 'opacity-25': forgotProcessing }"
                                        :disabled="forgotProcessing"
                                    >
                                        <span class="text-center text-sm capitalize dark:textDark">
                                            Email password reset link
                                        </span>
                                    </PrimaryButton>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
