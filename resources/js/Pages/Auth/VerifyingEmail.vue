<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useToastStore } from '@/Stores/ToastStore';

const props = defineProps({
    status: { type: String, required: true },
});

// the link can be opened on any device, so only send people home if this browser holds a token
const loggedIn = !!localStorage.getItem('token');
const target = loggedIn ? route('home') : route('login');
const ok = computed(() => props.status !== 'invalid');

const content = {
    'verified': { title: 'Email verified', body: 'Thanks, your email address has been verified.' },
    'already-verified': { title: 'Already verified', body: 'Your email address was already verified.' },
    'invalid': { title: 'Link not valid', body: 'This verification link is invalid. Log in to request a new one.' },
}[props.status] ?? { title: 'Link not valid', body: 'This verification link is invalid.' };

onMounted(() => {
    useToastStore().add({ message: content.body, type: ok.value ? 'success' : 'warning' });
    if (ok.value) {
        setTimeout(() => router.visit(target), 4000);
    }
});
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>
<template>
    <GuestLayout>
        <Head :title="content.title" />
        <div class="text-center" data-test="verify-result" :data-status="status">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full text-2xl"
                 :class="ok ? 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400'">
                {{ ok ? '✓' : '!' }}
            </div>
            <h1 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ content.title }}</h1>
            <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">{{ content.body }}</p>
            <Link :href="target"
                  class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white">
                {{ loggedIn ? 'Continue' : 'Log in' }}
            </Link>
            <p v-if="ok" class="mt-3 text-xs text-gray-500 dark:text-gray-500">Redirecting you shortly.</p>
        </div>
    </GuestLayout>
</template>
