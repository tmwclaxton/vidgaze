<script setup>

import {computed, onBeforeMount, ref} from "vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import Dropdown from "@/Components/Inputs/Dropdown.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import StudioTextInput from "@/Pages/Studio/Partials/StudioTextInput.vue";
import StudioTagsInput from "@/Pages/Studio/Partials/StudioTagsInput.vue";
import StudioMadeForKidsInput from "@/Pages/Studio/Partials/StudioMadeForKidsInput.vue";
import StudioImageInput from "@/Pages/Studio/Partials/StudioImageInput.vue";
import StudioPrimarySourceInput from "@/Pages/Studio/Partials/StudioPrimarySourceInput.vue";
import StudioCheck from "@/Pages/Studio/Partials/StudioCheck.vue";
import StudioVisibilityInput from "@/Pages/Studio/Partials/StudioVisibilityInput.vue";
import StudioPlatformsInput from "@/Pages/Studio/Partials/StudioPlatformsInput.vue";
import StudioCategoryInput from "@/Pages/Studio/Partials/StudioCategoryInput.vue";
import TertiaryButton from "@/Components/Buttons/TertiaryButton.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";


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


            <div  v-if="useAuthStore().user != null">
                <div class=" flex flex-col md:flex-row-reverse ">
                    <div class="display:initial   mx-8 md:mr-8 md:ml-0">
                        <div class="sticky top-36 pt-3 w-full md:w-56">
                            <consistent-content-holder class="">
                                <div class="pb-3  overflow-hidden rounded w-full  mb-2 md:ml-0 ">
                                    <div class="md:mx-12 my-4  flex justify-center">
                                        <img class="w-1/2 md:w-full aspect-square rounded-full "
                                             :src="useAuthStore().user.creator.avatar_url"
                                             alt="Profile picture">
                                    </div>
                                    <div class="px-4">
                                        <p class=" text-xs font-bold">Channel name</p>
                                        <div class="mx-2">
                                            <Link href=""
                                                  class=" text-xs   my-1  break-words font-semibold" v-text="useAuthStore().user.creator.name"></Link>
                                        </div>

                                        <p class=" mt-2 text-xs font-bold">Channel url</p>
                                        <div class="mx-2">
                                            <Link :href="route('channel.show',useAuthStore().user.creator.slug)"
                                                  class=" text-xs text-blue-700 overflow-ellipsis my-1 font-semibold" v-text="route('channel.show',useAuthStore().user.creator.slug)"></Link>
                                        </div>
                                    </div>
                                </div>
                            </consistent-content-holder>
                        </div>
                    </div>
        <div class="flex flex-grow">
          <ConsistentPadding >
              <TitleComponent :text="headTitle" class="">
                  <font-awesome-icon :icon="['fas', 'upload']"  class="w-6 h-6 my-auto"/>
              </TitleComponent>
            <form @submit.prevent="" class="mt-8 space-y-3 sm:min-w-[20rem] w-full ">
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
                <StudioMadeForKidsInput :value="form.audience"
                                         @update:model-value="form.audience = $event"
                                         @submit=""
                                         label="Made for kids"
                                         for="made_for_kids"
                                         :error_message="form.errors.audience ? form.errors.audience[0] : null"
                />

                <StudioVisibilityInput :value="form.visibility"
                                       :publish_time="form.publish_time || null"
                                       @update:model-value="form.visibility = $event"
                                       @submit=""
                                       label="Visibility"
                                       for="visibility"
                                       :errors="form.errors"
                />
                <StudioCategoryInput :value="form.category_id"
                                     :categories="categories"
                                     @update:model-value=""
                                     label="Category"
                                     for="category"
                                     :errors="form.errors"
                 />

                <StudioPlatformsInput :errors="form.errors"
                                          :preferred_source="form.preferred_source"
                                          :sources="['youtube']"
                                          @update:model-value="form.preferred_source = $event"
                 />

                <StudioPrimarySourceInput :preferred_source="form.preferred_source"
                                          :sources="['youtube']"
                                          @update:model-value="form.preferred_source = $event"
                                          @submit=""
                                          label="Primary Source"
                                          for="primary_source"
                                          :errors="form.errors"
                />
                <StudioCheck/>
                <div class="flex space-x-3 justify-center">
                    <div class="flex justify-center">
                        <TertiaryButton @click="handleSaveDraft" class="h-[3rem]" :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing">SAVE DRAFT
                        </TertiaryButton>
                    </div>
                    <div class="flex justify-center">
                        <TertiaryButton @click="handlePublish" class="h-[3rem]" :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing">{{ form.visibility === 'scheduled' ? 'SCHEDULE' : 'PUBLISH'}}
                        </TertiaryButton>
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
        </div>
    </div>
</div>
</template>

