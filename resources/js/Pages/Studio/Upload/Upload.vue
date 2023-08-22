<template>

    <Head title="Upload Video" />

    <ConsistentPadding class="-mt-4">
        <Title text="Upload Video">
            <!--                <StreamIcon class="w-6 h-6 my-auto"/>-->
        </Title>
        <form @submit.prevent="submit" class="space-y-4 sm:min-w-[20rem] w-screen sm:w-full px-6 sm:px-0">
            <DropZone @drop.prevent="drop" @change="selectedFile"/>
            <div class="max-w-md">
            </div>
        </form>
        <div>
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
import Title from "@/Components/General/Title.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import DropZone from "@/Pages/Studio/Upload/Partials/DropZone.vue";

let form = useForm({
    video: ''
});

const submit = () => {
    axios.post(route('api.studio.video.prime')).then((response) => {
        console.log('video draft created: ' + response.data.slug);

        const formData = new FormData();
        formData.append('video', form.video);
        axios.post(route('api.studio.video.upload', {slug: response.data.slug}), formData).then(
            () => { console.log('upload complete'); }
        );

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
