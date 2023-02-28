<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
        <Head title="Home">
            <title>Home</title>
        </Head>
    <AuthenticatedLayout>


    </AuthenticatedLayout>
        <!--<ContentHolder>-->



        <!--    <div id="video-holder" class="mt-[4rem] dark:mt-[4.5rem]">-->
        <!--        <div v-if="videos != null" class="px-3   xs:px-0 mb-10 grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4-->
        <!--                ml:grid-cols-4  4xl:grid-cols-6 gap-4 ">-->
        <!--            &lt;!&ndash;iterate through videos &ndash;&gt;-->
        <!--                <BoxVideo :channel_page="false" v-for="video in videos.data" :video="video" :key="video.id"></BoxVideo>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</ContentHolder>-->

</template>


