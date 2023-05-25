<template>
    <div>
        <Head title="Support" />

        <!--<section class=" h-[calc(100vh-4rem)] flex  ">-->
        <!--    <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-md my-auto">-->
        <!--        <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-center text-zinc-900 dark:text-white">Contact Us</h2>-->
        <!--        <p class="mb-8 lg:mb-16 font-light text-center text-zinc-500 dark:text-zinc-400 sm:text-xl">Found a technical issue? Want to send feedback about a beta feature? Need details about our Business plan? Let us know.</p>-->
        <!--        <div class="space-y-8">-->
        <!--            <div>-->
        <!--                <label for="name" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-300">Your name</label>-->
        <!--                <input v-model="name" type="name" id="name" class="shadow-sm bg-zinc-50 border border-zinc-300 text-zinc-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light"-->
        <!--                       placeholder="Tell us your name!" required>-->
        <!--            </div>-->
        <!--            <div>-->
        <!--                <label for="email" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-300">Your email</label>-->
        <!--                <input v-model="email" type="email" id="email" class="shadow-sm bg-zinc-50 border border-zinc-300 text-zinc-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light"-->
        <!--                       placeholder="What's your email?" required>-->
        <!--            </div>-->
        <!--            <div>-->
        <!--                <label for="subject" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-300">Subject</label>-->
        <!--                <input v-model="subject" type="text" id="subject" class="block p-3 w-full text-sm text-zinc-900 bg-zinc-50 rounded-lg border border-zinc-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light"-->
        <!--                       placeholder="Let us know how we can help you" required>-->
        <!--            </div>-->
        <!--            <div class="sm:col-span-2">-->
        <!--                <label for="message" class="block mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-400">Your message</label>-->
        <!--                <textarea v-model="message" id="message" rows="6" class="block p-2.5 w-full text-sm text-zinc-900 bg-zinc-50 rounded-lg shadow-sm border border-zinc-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"></textarea>-->
        <!--            </div>-->
        <!--            <PrimaryButton @click="submitForm" class="py-3 px-5 text-sm font-medium text-center rounded-lg sm:w-fit">Send message</PrimaryButton>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</section>-->
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
// import {useToastStore} from "@/Stores/ToastStore";
// const toast = useToastStore();
//
//
// const email = ref('');
// const name = ref('');
// const subject = ref('');
// const message = ref('');

// const submitForm = () => {
//     const formData = {
//         email: email.value,
//         name: name.value,
//         subject: subject.value,
//         message: message.value,
//     };

    // const url = route('support.email.send');
    //
    // axios
    //     .post(url, formData)
    //     .then(response => {
    //         // Handle successful response
    //         console.log(response.data);
    //         toast.add({
    //             message: 'Your message has been sent!',
    //             type: 'success',
    //         });
    //         //reset form
    //         email.value = '';
    //         name.value = '';
    //         subject.value = '';
    //         message.value = '';
    //
    //     })
    //     .catch(error => {
    //         // Handle error
    //         console.error(error);
    //     });
// };
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>

<style scoped>
/* Your component-specific styles */
</style>
