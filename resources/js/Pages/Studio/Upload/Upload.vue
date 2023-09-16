
<script setup>

import {ref} from "vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import DropZone from "@/Pages/Studio/Upload/Partials/DropZone.vue";
import {useToastStore} from "@/Stores/ToastStore";

let form = useForm({
    video: ''
});

const submit = () => {
    // check if file type is video
    if (form.video.type !== 'video/mp4') {
        useToastStore().add({
            message: 'Please upload a video file',
            type: 'warning'
        });
        return;
    }

    axios.post(route('api.studio.video.prime')).then((response) => {
        console.log('video draft created: ' + response.data.slug);

        const formData = new FormData();
        formData.append('video', form.video);
        axios.post(route('api.studio.video.upload', {slug: response.data.slug}), formData).then(
        () => {
            console.log('upload complete');
        });

        // redirect to studio.video.edit with slug given in response
        router.get(route('studio.video.draft.edit', {slug: response.data.slug}));
    }).catch((error) => {
        console.log(error);
    });

};


let dropzoneFile = ref("");

const drop = (event) =>{
    dropzoneFile.value = event.dataTransfer.files[0];
    form.video = dropzoneFile.value;
    submit();
}

const selectedFile = () =>{
    dropzoneFile.value = document.querySelector('#file').files[0];
    form.video = dropzoneFile.value;
    submit();
}
</script>
<template>

    <Head title="Upload Video" />

    <ConsistentPadding>
        <TitleComponent text="VidGaze MultiUploader" >
            <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" class="w-6 h-6 my-auto"/>
        </TitleComponent>
        <p>Upload to YouTube, TikTok, Dailymotion and Vimeo all at the same time.</p>
        <form @submit.prevent="submit" class="space-y-4 w-full mt-10 h-[calc(100vh-14rem)]">
            <DropZone @drop.prevent="drop" @change="selectedFile"/>
            <div class="max-w-md">
            </div>
        </form>
        <div>
        </div>
    </ConsistentPadding>
</template>

