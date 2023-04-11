<script setup>






</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,

};
</script>
<template>
    <!--    <Head title="Landing" />-->
    <div class="h1 text dark:textDark">



        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-2 2xl:px-10 ">
                <div class="  overflow-hidden sm:rounded-lg">
                    <!--Welcome message + name-->
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-200">Welcome back, {{ $page.props.auth.user.creator.name }}!</h1>

                    <!-- link to component playground-->
                    <a href="/component-testing" class="underline text-blue-500">Components overview</a>
                </div>
            </div>
        </div>
    </div>
</template>
