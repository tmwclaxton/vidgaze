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
    <Head title="Popular Streams" />


                <div class=" mx-auto   h-[calc(100vh-4rem)] py-auto justify-center flex flex-col">
                    <div class="mx-10 my-auto flex-col  flex   dark:bg-transparent  ">

                        <div class="flex flex-col space-y-2 items-center justify-center">
                            <div class="w-52 h-52 rounded-full p-8 bg-zinc-300 dark:bg-zinc-200 animate-bou nce">
                                <!--<font-awesome-icon :icon="['fas', 'wrench']" class="w-full h-full text-zinc-600 dark:text-zinc-400 animate-bounce"/>-->
                                <font-awesome-icon :icon="['fas', 'otter']"  class="w-full h-full text-zinc-600 dark:text-vidgaze-blue"/>
                            </div>
                            <p class="  text-zinc-600 dark:textDark font-bold text-4xl pt-8 text-center">VidGaze Music is Under Construction</p>
                            <!--<p class="text dark:textDark text-sm justify-center text-justify ">-->
                            <!--    VidGaze Music will be composed of ITunes, YouTube Music and SoundCloud and will be a feature of the next update!-->
                            <!--</p>-->

                        </div>
                    </div>

                </div>

</template>
