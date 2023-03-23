<script setup>
import { ref } from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
const showingNavigationDropdown = ref(false);

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: false,
        default: false
    },
})
</script>

<template>



    <div>
        <ToastList/>
        <div class="  ">

            <Nav :showingNavigationDropdown="showingNavigationDropdown" :showingStudioLinks="showingStudioLinks"/>
            <!-- Page Heading -->
            <header class="bg-white dark:bg-zinc-900 shadow" v-if="$slots.header">
                <div class="max-w-screen-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main >
                <slot />
            </main>
        </div>
    </div>
</template>
