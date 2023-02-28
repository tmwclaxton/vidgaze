<script setup>
import { ref } from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
const showingNavigationDropdown = ref(false);
</script>

<template>



    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

            <Nav :showingNavigationDropdown="showingNavigationDropdown"/>
            <!-- Page Heading -->
            <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
