<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>

<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
</script>

<template>
    <Head title="Page not found" />

    <ConsistentPadding>
        <section class="flex flex-col items-center text-center py-16 gap-6">
            <img src="/images/mascot/Confused.png" alt="Confused VidGaze astronaut mascot" class="h-48 w-auto object-contain" />
            <p class="text-base font-bold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Error 404</p>
            <h1 class="text-3xl md:text-4xl font-bold">We couldn't find that page</h1>
            <p class="text-lg max-w-xl text-zinc-600 dark:text-zinc-300">
                The link may be broken, or the page may have moved. Try searching for a video or channel, or head back home.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <Link :href="route('landing')" class="px-6 py-2 rounded-lg bg-sky-600 text-white font-semibold hover:opacity-90">
                    Go to the home page
                </Link>
                <Link :href="route('category.index')" class="px-6 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    Browse categories
                </Link>
            </div>
        </section>
    </ConsistentPadding>
</template>
