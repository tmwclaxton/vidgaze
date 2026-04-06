<script setup>
import { Head } from '@inertiajs/vue3';
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout
};
</script>
<template>
    <Head title="VidGaze Music" />

    <div class="mx-auto flex min-h-[calc(100vh-6rem)] max-w-lg flex-col justify-center px-6 py-16 sm:px-8">
        <div
            class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-10 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900/60 dark:shadow-zinc-950/40"
        >
            <div
                class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-950/80 dark:text-sky-300"
            >
                <font-awesome-icon :icon="['fas', 'music']" class="h-9 w-9" />
            </div>
            <h1 class="mt-8 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                Music is coming to VidGaze
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                We're building a space for tracks and catalogs from across the web. Check back soon.
            </p>
        </div>
    </div>

</template>
