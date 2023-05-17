
<script setup>
const name = 'CategoryCard';
const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div  class='w-full flex flex-col gap-1 group'>
        <a :href="route('category.show',{slug:category.slug})" class="cursor-pointer">
            <!-- Image -->
            <div >
                <img :src="category.thumbnail_url"
                     class="w-full rounded-xl hover:translate-x-1 hover:-translate-y-1 delay-50 duration-100" />
            </div>
            <!-- Games Title -->
            <p class=" text-base font-bold mt-2 mb-1" v-text="category.name"/>
        </a>
        <!-- Category Tags -->
        <div class="flex flex-row flex-wrap gap-2">
            <div v-for="tag in category.tags" class="cursor-pointer px-3 p-1 rounded-full text-xs font-bold bg-zinc-200 dark:bg-zinc-700">
                <p v-text="tag"/>
            </div>
        </div>
    </div>
</template>
