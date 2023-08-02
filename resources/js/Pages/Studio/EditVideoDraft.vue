<template>

        <Head title="Upload Video" />

        <ConsistentPadding class="mt-4">
            <Title text="Upload Video">
<!--                <StreamIcon class="w-6 h-6 my-auto"/>-->
            </Title>
        <form @submit.prevent="" class="space-y-4 sm:min-w-[20rem] w-full sm:px-6">
            <div>
                <InputLabel class="mb-1" for="title" value="Title"/>
                <TextInput type="text" title="title" id="title" placeholder="Title" v-model="form.title" class="w-full" required/>
                <InputError class="mt-2" :message="form.errors.title"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="description" value="Description"/>
                <TextArea
                    id="description"
                    v-model="form.description"
                    rows="4"
                />
                <InputError :message="form.errors.description"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="tags" value="Tags"/>
                <TagInput v-model="form.tags"/>
                <InputError :message="form.errors.tags"/>
            </div>
            <div class="max-w-md">
                <InputLabel class="mb-1" for="thumbnail" value="Thumbnail"/>
                <input type="file" name="thumbnail" id="thumbnail" @input="selectedFileThumbnail">
                <InputError class="mt-1" :message="form.errors.thumbnail"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="audience" value="Audience"/>
                <Dropdown
                    v-model="form.audience"
                    name="audience"
                    id="audience"
                    :items="audiences"
                    required/>
                <InputError class="mt-2" :message="form.errors.audience"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="visibility" value="Visibility"/>
                <div class="space-y-2">
                    <div>
                        <input type="radio" id="private" value="private" v-model="form.visibility" class="mr-2">
                        <label for="private">Private</label>
                    </div>
                    <div>
                        <input type="radio" id="unlisted" value="unlisted" v-model="form.visibility" class="mr-2">
                        <label for="unlisted">Unlisted</label>
                    </div>
                    <div>
                        <input type="radio" id="public" value="public" v-model="form.visibility" class="mr-2">
                        <label for="public">Public</label>
                    </div>
                    <div>
                        <input type="radio" id="scheduled" value="scheduled" v-model="form.visibility" class="mr-2">
                        <label for="scheduled">Schedule</label>
                        <DateInput class="mt-2" v-if="form.visibility === 'scheduled'" v-model="form.publish_time"/>
                        <InputError class="mt-2" :message="form.errors.publish_time"/>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.visibility"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="collection" value="Category"/>
                <Dropdown
                    v-model="form.category_id"
                    name="category_id"
                    id="category_id"
                    :items="categories"
                    required
                />
                <InputError class="mt-2" :message="form.errors.category_id"/>
            </div>
            <div>
                <InputLabel class="mb-1" for="platforms" value="Platforms"/>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="youtube" value="youtube" v-model="form.platforms" class="mr-2">
                        <YouTubeIcon class="w-6 h-6 mr-2"/>
                        <label for="youtube">YouTube</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="dailymotion" value="dailymotion" v-model="form.platforms" class="mr-2">
                        <DailyMotionIcon class="w-6 h-6 mr-2"/>
                        <label for="dailymotion">Dailymotion</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="vimeo" value="vimeo" v-model="form.platforms" class="mr-2">
                        <VimeoIcon class="w-6 h-6 mr-2"/>
                        <label for="vimeo">Vimeo</label>
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
                                   :disabled="form.processing">{{ form.visibility === 'scheduled' ? 'SCHEDULE' : 'PUBLISH'}}
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
import TagInput from "@/Components/Inputs/TagInput.vue";
import Dropdown from "@/Components/Inputs/Dropdown.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";
import YouTubeIcon from '#icons/youtube.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';


let props = defineProps({
    video: Object,
    categories: Object,
});

const audiences = [
    { value: 'all', name: 'Everyone' },
    { value: 'kids', name: 'Kids' },
    { value: 'mature', name: 'Mature' },
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
});


const handleSaveDraft = () => {
    form.put(route('studio.video.update', [props.video.slug]), {
        preserveScroll: true,
    });

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
