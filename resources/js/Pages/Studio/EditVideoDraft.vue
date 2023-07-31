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
                <TagInput class="mt-2" v-model="form.tags"/>
                <InputError :message="form.errors.tags"/>
            </div>
            <div class="max-w-md">
                <InputLabel for="thumbnail" value="Thumbnail" class="mb-1"/>
                <input type="file" name="thumbnail" id="thumbnail" @input="selectedFileThumbnail">
                <InputError class="mt-1" :message="form.errors.thumbnail"/>
            </div>
            <div>
                <InputLabel for="audience" value="Audience"/>
                <Dropdown
                    v-model="form.audience"
                    name="audience"
                    id="audience"
                    :items="audiences"
                    required/>
                <InputError class="mt-2" :message="form.errors.audience"/>
            </div>
            <div>
                <InputLabel for="visibility" value="Visibility"/>
<!--                <<select required v-model="form.visibility" name="visibility" id="visibility" class="border-gray-300 focus:focus-pantone rounded-md shadow-sm w-full focus:focus-pantone focus:border-pantone focus:ring-pantone">-->
<!--                    <option v-for="visibility in visibilities" :value="visibility.value">{{ visibility.name }}</option>-->
<!--                </select>>-->
                <Dropdown
                    v-model="form.visibility"
                    name="visibility"
                    id="visibility"
                    :items="visibilities"
                    required/>
                <InputError class="mt-2" :message="form.errors.visibility"/>
            </div>
            <div>
                <InputLabel for="collection" value="Category"/>
                <Dropdown
                    v-model="form.category_id"
                    name="category_id"
                    id="category_id"
                    :items="categories"
                    required
                />
                <InputError class="mt-2" :message="form.errors.visibility"/>
            </div>
            <div>
                <InputLabel for="publish_time" value="Schedule Publish Time"/>
<!--                checkbox use publish_time-->
                <div class="h-12 flex space-x-4 items-center">
                    <input type="checkbox" name="use_publish_time" id="use_publish_time" v-model="form.use_publish_time" class="mr-2">
                    <DateInput v-if="form.use_publish_time" v-model="form.publish_time"/>
                </div>
                <InputError class="mt-2" :message="form.errors.use_publish_time"/>

                <InputError class="mt-2" :message="form.errors.publish_time"/>
            </div>
            <!--            list of checkboxes for what platforms to upload to-->
            <div>
                <InputLabel for="platforms" value="Platforms"/>
                <div class="">
                    <div v-for="platform in platforms" :key="platform.value" class="flex items-center">
                        <input type="checkbox" :id="platform.value" :value="platform.value" v-model="form.platforms" class="mr-2">
                        <name :for="platform.value">{{ platform.name }}</name>
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

import {computed, ref} from "vue";
import {Head, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Title from "@/Components/General/Title.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TextArea from "@/Components/Inputs/TextArea.vue";
import TagInput from "@/Components/Inputs/TagInput.vue";
import Dropdown from "@/Components/Inputs/Dropdown.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";


let props = defineProps({
    video: Object,
    categories: Object,
});


const platforms = [
    { value: 'youtube', name: 'YouTube' },
    { value: 'dailymotion', name: 'Dailymotion' },
    { value: 'vimeo', name: 'Vimeo' },
];

const audiences = [
    { value: 'all', name: 'Everyone' },
    { value: 'kids', name: 'Kids' },
    { value: 'mature', name: 'Mature' },
];

const visibilities = [
    { value: 'public', name: 'Public' },
    { value: 'private', name: 'Private' },
    { value: 'unlisted', name: 'Unlisted' },
];

let form = useForm({
    title: props.video.title,
    description: props.video.description,
    tags: props.video.tags,
    thumbnail: '',
    category_id: props.video.category_id,
    visibility: props.video.visibility,
    publish_time: props.video.publish_time,
    audience: props.video.audience,
    platforms: props.video.platforms,
    use_publish_time: props.video.use_publish_time,
});

// set computed for use_publish_time
const usePublishTime = computed({
    get: () => {
        return form.use_publish_time;
    },
    set: (value) => {
        form.use_publish_time = value;
    }
});


const handleSaveDraft = () => {
    form.put(route('studio.video.update', [props.video.slug]));

    // const formData = new FormData();
    // formData.append('title', form.title);
    // formData.append('description', form.description);
    // formData.append('tags', form.tags);
    // formData.append('thumbnail', form.thumbnail);
    // formData.append('visibility', form.visibility);
    // formData.append('publish_time', form.publish_time);
    // formData.append('audience', form.audience);
    // formData.append('platforms', form.platforms);
    // formData.append('use_publish_time', form.use_publish_time);
    // formData.append('category_id', form.category_id);
    //
    //
    // axios.put(route('studio.video.update', [props.video.slug]), formData).then(
    //     () => { router.get(route('studio.dashboard')) }
    // ).catch((error) => {
    //     console.log(error);
    // })
};

const handlePublish = () => {
    form.post(route('studio.video.publish', [props.video.slug]));
};


let thumbnailValue = ref("");
const selectedFileThumbnail = () =>{
    thumbnailValue.value = document.querySelector('#thumbnail').files[0];
    form.thumbnail = thumbnailValue.value;
}


// function getImageURL(image) {
//     return URL.createObjectURL(image);
// }
</script>
