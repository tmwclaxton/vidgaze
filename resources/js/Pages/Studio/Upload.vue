<template>

    <div >
        <Head title="Upload Video" />
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl">Upload Video</h1>
        </div>
        <form @submit.prevent="submit" class="space-y-4 sm:min-w-[20rem] w-screen sm:w-full px-6 sm:px-0">
            <div>
                <InputLabel for="title" value="Title"/>
                <TextInput type="text" title="title" id="title" placeholder="Name" v-model="form.title" class="w-full" required/>
                <InputError class="mt-2" :message="form.errors.title"/>
            </div>
            <div>
                <InputLabel for="description" value="Description"/>
                <textarea
                    id="description"
                    class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone"
                    v-model="form.description"
                    rows="4"
                    required
                ></textarea>
                <InputError :message="form.errors.description"/>
            </div>
            <div>
                <InputLabel for="tags" value="Tags"/>
                <textarea
                    id="tags"
                    class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone"
                    v-model="form.tags"
                    rows="4"
                    required
                ></textarea>
                <InputError :message="form.errors.tags"/>
            </div>
            <div class="max-w-md">
                <InputLabel for="thumbnail" value="Thumbnail" class="mb-1"/>
                <input type="file" name="thumbnail" id="thumbnail" @input="selectedFileThumbnail">
                <InputError class="mt-1" :message="form.errors.thumbnail"/>
            </div>
            <div class="max-w-md">
                <InputLabel for="video" value="Video" class="mb-1"/>
                <input type="file" name="video" id="video" @input="selectedFileVideo">
                <InputError class="mt-1" :message="form.errors.video"/>
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
    </div>
</template>

<script setup>

import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";

let props = defineProps({
    categories: Object,
});

const platforms = [
    { value: 'youtube', label: 'YouTube' },
    { value: 'dailymotion', label: 'Dailymotion' },
    { value: 'vimeo', label: 'Vimeo' },
];

const audiences = [
    { value: 'everyone', label: 'Everyone' },
    { value: 'kids', label: 'kids' },
    { value: 'mature', label: 'mature' },
];

const visibilities = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
    { value: 'unlisted', label: 'Unlisted' },
];

let form = useForm({
    title: '',
    description: '',
    tags: '',
    thumbnail: '',
    video: '',
    // category: '',
    visibility: 'public',
    publishTime: '',
    audience: 'everyone',
    platforms: ['youtube', 'dailymotion', 'vimeo'],
    usePublishTime: false,
});

const submit = () => {
    form.post(route('studio.upload'));
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
