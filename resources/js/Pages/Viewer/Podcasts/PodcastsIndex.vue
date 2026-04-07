<script setup>
import InfinitePodcasts from "@/Components/ContentRows/InfinitePodcasts.vue";
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout
};
</script>
<template>
    <SeoHead
        title="Podcasts"
        description="Discover and listen to podcasts on VidGaze—episodes, shows, and creators in one place."
    />

    <div class="mx-auto max-w-[1680px] px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
        <header class="mb-8 border-b border-zinc-200 dark:border-zinc-800 pb-8">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Podcasts</h1>
        </header>

        <InfinitePodcasts />
    </div>
</template>
