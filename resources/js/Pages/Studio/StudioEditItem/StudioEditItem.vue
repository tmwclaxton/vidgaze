<script setup>

import {computed, onBeforeMount, ref} from "vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import Dropdown from "@/Components/Inputs/Dropdown.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";
import YouTubeIcon from '#icons/youtube.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import StudioTextInput from "@/Pages/Studio/Partials/StudioTextInput.vue";
import StudioTagsInput from "@/Pages/Studio/Partials/StudioTagsInput.vue";
import StudioMadeForKidsInput from "@/Pages/Studio/Partials/StudioMadeForKidsInput.vue";
import StudioImageInput from "@/Pages/Studio/Partials/StudioImageInput.vue";
import StudioPrimarySourceInput from "@/Pages/Studio/Partials/StudioPrimarySourceInput.vue";
import StudioCheck from "@/Pages/Studio/Partials/StudioCheck.vue";
import StudioVisibilityInput from "@/Pages/Studio/Partials/StudioVisibilityInput.vue";


let categories = ref([]);
let video = ref({});
let props = defineProps({
    slug: String,
    type: String,
});
let key = ref('');

onBeforeMount(() => {
    axios.get(route('api.studio.video.draft.getEdit', [props.slug])).then((response) => {
        categories.value = response.data.categories;
        video.value = response.data.video;
        form = useForm({
            title: video.value.title,
            description: video.value.description,
            tags: video.value.tags,
            thumbnail: '',
            category_id: video.value.category_id,
            visibility: video.value.visibility,
            publish_time: video.value.publish_time,
            audience: video.value.audience,
            platforms: video.value.platforms,
        });
        // convert unix time to local date
        // if publish time before now, set to now
        if (form.publish_time < Math.floor((new Date().getTime()) / 1000)) {
            form.publish_time = Math.floor((new Date().getTime()) / 1000);
        }
        form.publish_time = new Date(form.publish_time).getTime() - new Date().getTimezoneOffset() * 60;

        key.value = Math.random().toString(36)
    })
})


let form = useForm({
    title: video.value.title,
    description: video.value.description,
    tags: video.value.tags,
    thumbnail: '',
    category_id: video.value.category_id,
    visibility: video.value.visibility,
    publish_time: video.value.publish_time,
    audience: video.value.audience,
    platforms: video.value.platforms,
    preferred_source: video.value.preferred_source,
});


function prepareFormData(){
    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('description', form.description);
    form.tags.forEach((tag) => {
        formData.append('tags[]', tag);
    });
    formData.append('category_id', form.category_id);
    formData.append('thumbnail', form.thumbnail);
    formData.append('visibility', form.visibility);

    // convert local publish time to unix integer
    let publish_time = Math.floor(new Date(form.publish_time).getTime());
    formData.append('publish_time', publish_time);
    formData.append('audience', form.audience);
    form.platforms.forEach((platform) => {
        formData.append('platforms[]', platform);
    });
    return formData;
}
const handleSaveDraft = () => {
    axios.put(route('api.studio.video.draft.update', [video.value.slug]), prepareFormData()).then(() => {
            router.get(route('studio.content'))
        }
    ).catch((error) => {
        form.errors = error.response.data.errors || {};
        console.log(error);
    })
};

const handlePublish = () => {
    axios.post(route('api.studio.video.publish', [video.value.slug]), prepareFormData()).then(() => {
            router.get(route('studio.content'))
        }
    ).catch((error) => {
        form.errors = error.response.data.errors || {};
        console.log(error);
    });


    // form.post(route('studio.video.publish', [props.video.slug]), {
    //     preserveScroll: true,
    // });
};


let thumbnailValue = ref("");
const selectedFileThumbnail = () =>{
    thumbnailValue.value = document.querySelector('#thumbnail').files[0];
    form.thumbnail = thumbnailValue.value;
}

const headTitle = computed(() => {
    // depends on props.type
    if (props.type === 'video_draft') {
        return 'Upload Video';
    } else if (props.type === 'video') {
        return 'Edit Video';
    } else if (props.type === 'stream') {
        return 'Edit Stream';
    }
});
</script>

<template>

        <Head :title="headTitle" />

        <ConsistentPadding >
            <TitleComponent :text="headTitle" class="">
                <font-awesome-icon :icon="['fas', 'upload']"  class="w-6 h-6 my-auto"/>
            </TitleComponent>
        <form @submit.prevent="" class="mt-5 space-y-4 sm:min-w-[20rem] w-full ">
            <StudioTextInput :value="form.title || ''"
                             @update:model-value="form.title = $event"
                             @submit=""
                             label="Title"
                             placeholder="Video title"
                             for="title"
                             :error_message="form.errors.title ? form.errors.title[0] : null"
            />
            <StudioTextInput :value="form.description || ''"
                             @update:model-value="form.description = $event"
                             @submit=""
                             label="Description"
                             placeholder="Video description"
                             for="description"
                             :enter-submit="false"
                             :maxlength="1000"
                             :error_message="form.errors.description ? form.errors.description[0] : null"
            />
            <StudioTagsInput :value="form.tags || []"
                             @update:model-value="form.tags = $event"
                             @submit=""
                             label="Tags"
                             for="tags"
                             :error_message="form.errors.tags ? form.errors.tags[0] : null"
            />
            <StudioImageInput :value="form.thumbnail || ''"
                              @update:model-value="form.thumbnail = $event"
                              @submit=""
                              label="Thumbnail"
                              for="thumbnail"
                              :error_message="form.errors.thumbnail ? form.errors.thumbnail[0] : null"
            />
            <StudioMadeForKidsInput :value="form.audience || null"
                                     @update:model-value="form.audience = $event"
                                     @submit=""
                                     label="Made for kids"
                                     for="made_for_kids"
                                     :error_message="form.errors.audience ? form.errors.audience[0] : null"
            />

            <StudioVisibilityInput :value="form.visibility || null"
                                   :publish_time="form.publish_time || null"
                                   @update:model-value="form.visibility = $event"
                                   @submit=""
                                   label="Visibility"
                                   for="visibility"
                                   :errors="form.errors"
            />

            <div>
                <InputLabel class="mb-1" for="collection" value="Category"/>
                <Dropdown
                    v-model="form.category_id"
                    name="category_id"
                    id="category_id"
                    :items="categories"
                    required
                />
                <InputError class="mt-2" :message="form.errors.category_id ? form.errors.category_id[0] : null"/>
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
                <InputError class="mt-2" :message="form.errors.platforms ? form.errors.platforms[0] : null"/>
            </div>
            <StudioPrimarySourceInput :preferred_source="form.preferred_source"
                                      :sources="['youtube']"
                                      @update:model-value="form.preferred_source = $event"
                                      @submit=""
                                      label="Primary Source"
                                      for="primary_source"
                                      :error_message="form.errors.preferred_source ? form.errors.preferred_source[0] : null"
            />
            <StudioCheck/>
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

