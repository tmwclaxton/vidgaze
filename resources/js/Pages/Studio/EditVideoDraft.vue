<template>

        <Head title="Upload Video" />

        <ConsistentPadding class="-mt-4">
            <Title text="Upload Video">
<!--                <StreamIcon class="w-6 h-6 my-auto"/>-->
            </Title>
        <form @submit.prevent="" class="space-y-4 sm:min-w-[20rem] w-screen sm:w-full px-6 sm:px-0">
            <div>
                <InputLabel for="title" value="Title"/>
                <TextInput type="text" title="title" id="title" placeholder="Title" v-model="form.title" class="w-full" required/>
                <InputError class="mt-2" :message="form.errors.title"/>
            </div>
            <div>
                <InputLabel for="description" value="Description"/>
                <TextArea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    required
                />
                <InputError :message="form.errors.description"/>
            </div>
            <div>
                <InputLabel for="tags" value="Tags"/>
                <TextArea
                    id="tags"
                    v-model="form.tags"
                    rows="4"
                    required
                ></TextArea>
                <InputError :message="form.errors.tags"/>
            </div>
            <div class="max-w-md">
                <InputLabel for="thumbnail" value="Thumbnail" class="mb-1"/>
                <input type="file" name="thumbnail" id="thumbnail" @input="selectedFileThumbnail">
                <InputError class="mt-1" :message="form.errors.thumbnail"/>
            </div>
<!--            <div>-->
<!--                <InputLabel for="category" value="Category"/>-->
<!--                <select required v-model="form.category" name="category" id="category" class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone">-->
<!--                    <option v-for="category in categories" :value="category.value">{{ category.label }}</option>-->
<!--                </select>-->
<!--                <InputError class="mt-2" :message="form.errors.category"/>-->
<!--            </div>-->
            <div>
                <InputLabel for="audience" value="Audience"/>
                <select required v-model="form.audience" name="audience" id="audience" class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone">
                    <option v-for="audience in audiences" :value="audience.value">{{ audience.label }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.audience"/>
            </div>
            <div>
                <InputLabel for="collection" value="Visibility"/>
                <select required v-model="form.visibility" name="visibility" id="visibility" class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone">
                    <option v-for="visibility in visibilities" :value="visibility.value">{{ visibility.label }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.visibility"/>
            </div>
            <div>
                <InputLabel for="publishTime" value="Publish Time"/>
                <input type="checkbox" id="publishTimeCheckbox" v-model="form.usePublishTime" class="mr-2">
                <input type="datetime-local" name="publishTime" id="publishTime" v-model="form.publishTime" class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone">
                <InputError class="mt-2" :message="form.errors.publishTime"/>
            </div>
            <!--            list of checkboxes for what platforms to upload to-->
            <div>
                <InputLabel for="platforms" value="Platforms"/>
                <div class="">
                    <div v-for="platform in platforms" :key="platform.value" class="flex items-center">
                        <input type="checkbox" :id="platform.value" :value="platform.value" v-model="form.platforms" class="mr-2">
                        <label :for="platform.value">{{ platform.label }}</label>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.platforms"/>
            </div>
            <div class="flex space-x-3 justify-center">
                <div class="flex justify-center">
                    <PrimaryButton @click="handleSaveDraft" class="h-[3rem]" :class="{ 'opacity-25': form.processing }"
                                   :disabled="form.processing">SAVE DRAFT
                    </PrimaryButton>
                </div>

                <div class="flex justify-center">
                    <PrimaryButton @click="handlePublish" class="h-[3rem]" :class="{ 'opacity-25': form.processing }"
                                   :disabled="form.processing">PUBLISH
                    </PrimaryButton>
            </div>
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
import {Head, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Title from "@/Components/General/Title.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TextArea from "@/Components/Inputs/TextArea.vue";
import FileUpload from "@/Components/Inputs/FileUpload.vue";


let props = defineProps({
    video: Object,
    categories: Object,
});

const platforms = [
    { value: 'youtube', label: 'YouTube' },
    { value: 'dailymotion', label: 'Dailymotion' },
    { value: 'vimeo', label: 'Vimeo' },
];

const audiences = [
    { value: 'all', label: 'Everyone' },
    { value: 'kids', label: 'Kids' },
    { value: 'mature', label: 'Mature' },
];

const visibilities = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
    { value: 'unlisted', label: 'Unlisted' },
];

let form = useForm({
    title: props.video.title,
    description: props.video.description,
    tags: props.video.tags,
    thumbnail: '',
    // category: '',
    visibility: props.video.visibility,
    publishTime: props.video.publishTime,
    audience: props.video.audience,
    platforms: props.video.platforms,
    usePublishTime: props.video.usePublishTime,
});

// const submit = () => {
//     form.put(route('studio.video.update', [props.video.slug]));
// };

const handleSaveDraft = () => {
    form.put(route('studio.video.update', [props.video.slug]));
};

const handlePublish = () => {
    form.post(route('studio.video.publish', [props.video.slug]));
};


let thumbnailValue = ref("");
const selectedFileThumbnail = () =>{
    thumbnailValue.value = document.querySelector('#thumbnail').files[0];
    form.thumbnail = thumbnailValue.value;
}

let videoValue = ref("");
const selectedFileVideo = () =>{
    videoValue.value = document.querySelector('#video').files[0];
    form.video = videoValue.value;
}

// function getImageURL(image) {
//     return URL.createObjectURL(image);
// }
</script>
