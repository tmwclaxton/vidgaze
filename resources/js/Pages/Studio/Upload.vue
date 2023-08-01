<template>

        <Head title="Upload Video" />

        <ConsistentPadding class="-mt-4">
            <Title text="Upload Video">
<!--                <StreamIcon class="w-6 h-6 my-auto"/>-->
            </Title>
        <form @submit.prevent="submit" class="space-y-4 sm:min-w-[20rem] w-screen sm:w-full px-6 sm:px-0">
            <div class="max-w-md">
                <InputLabel for="video" value="Video" class="mb-1"/>
                <input type="file" name="video" id="video" @input="selectedFileVideo">
                <InputError class="mt-1" :message="form.errors.video"/>
            </div>
            <div class="flex justify-center">
                <PrimaryButton class="h-[3rem]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">UPLOAD</PrimaryButton>
            </div>
        </form>
        <div>
            <!--            <div>-->
            <!--                <div  :style="{'background-image': 'url(' + getImageURL(image) + ')'}" v-for="image in form.images" class="w-20 h-20 bg-cover bg-center"></div>-->
            <!--                <img v-for="image in images" :src="getImageURL(image)" :key="image.name"  alt="image"/>-->
            <!--            </div>-->
        </div>
        </ConsistentPadding>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    layout: AuthenticatedLayout,

};
</script>
<script setup>

import {ref} from "vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Title from "@/Components/General/Title.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {Inertia} from "@inertiajs/inertia";


let props = defineProps({
    categories: Object,
});

let form = useForm({
    video: ''
});

const submit = () => {
    axios.post(route('studio.video.prime')).then((response) => {
        console.log('video draft created: ' + response.data.slug);

        const formData = new FormData();
        formData.append('video', form.video);
        axios.post(route('studio.video.upload', {slug: response.data.slug}), formData).then(
            () => { console.log('upload complete'); }
        );

        // redirect to studio.video.edit with slug given in response
        router.get(route('studio.video.edit', {slug: response.data.slug}));
    }).catch((error) => {
        console.log(error);
    });
};



let videoValue = ref("");
const selectedFileVideo = () =>{
    videoValue.value = document.querySelector('#video').files[0];
    form.video = videoValue.value;
}

</script>
