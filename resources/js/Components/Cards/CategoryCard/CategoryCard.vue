<script setup>
import { Link } from '@inertiajs/vue3';

const name = 'CategoryCard';
const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="group flex w-full flex-col gap-1">
        <Link
            :href="route('category.show', { slug: category.slug })"
            class="block cursor-pointer rounded-xl ring-1 ring-transparent transition-all duration-200 hover:ring-cyan-400/35 hover:shadow-[0_0_22px_-10px_rgba(34,211,238,0.2)]"
        >
            <!-- Image -->
            <div class="overflow-hidden rounded-xl">
                <img
                    :src="category.thumbnail_url"
                    class="w-full rounded-xl delay-50 duration-100 hover:-translate-y-1 hover:translate-x-1"
                />
            </div>
            <!-- Games Title -->
            <p
                class="mt-2 mb-1 bg-gradient-to-r from-violet-500 to-cyan-500 bg-clip-text text-base font-bold text-transparent dark:from-violet-400 dark:to-cyan-400"
                v-text="category.name"
            />
        </Link>
        <!-- Category Tags -->
        <div class="flex flex-row flex-wrap gap-2">
            <div
                v-for="tag in category.tags"
                class="cursor-pointer rounded-full bg-zinc-200 px-3 p-1 text-xs font-bold ring-1 ring-transparent transition-all duration-200 hover:ring-fuchsia-400/45 hover:shadow-[0_0_10px_-4px_rgba(232,121,249,0.28)] dark:bg-zinc-700"
            >
                <p v-text="tag" />
            </div>
        </div>
    </div>
</template>
