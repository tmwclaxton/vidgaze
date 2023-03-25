<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import toast from "@/Stores/toast";
import ScrollParallax from 'vue3-parallax/src/components/ScrollParallax.vue';
import SearchIcon from '~/images/icons/search.svg';
import {onMounted, onUnmounted, ref} from "vue";

const submit = () => {
    form.post("/test");
}

const addToast = () => {
    toast.add({
        message: "Hello from dashboard",
    });
}




</script>
<style>
.target {
    transition: opacity 0.6s ease;
}
</style>

<template>
    <!--    <Head title="Landing" />-->
    <AuthenticatedLayout>
        <div class="-mt-16 "></div>
                <div class="mt-80">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="  overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">You're logged in!</div>
                            <!--<ClockSVG class="w-5 fill-blue-400"/>-->
                            <div v-if="$page.props.flash.success" class="alert">
                                {{ $page.props.flash.success }}
                            </div>

                            <button @click="addToast">Add toast</button>
                        </div>
                    </div>
                </div>


    </AuthenticatedLayout>
</template>
