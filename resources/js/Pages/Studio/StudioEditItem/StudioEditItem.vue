<script setup>

import {computed, onBeforeMount, onMounted, ref} from "vue";
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
import StudioEditItemButton from "@/Pages/Studio/StudioEditItem/Partials/StudioEditItemButton.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useToastStore} from "@/Stores/ToastStore";

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

const type = computed(() => {
    if (props.type === 'video_draft') {
        return 'video draft';
    } else if (props.type === 'video') {
        return 'video';
    } else if (props.type === 'stream') {
        return 'stream';
    }
});

const tab = ref('details');
let props = defineProps({
    slug: String,
    type: String,
});
const categories = ref([]);
const item = ref(null);
const key = ref('');
let form = useForm({});


onMounted(() => {
    getItem();
});

const getItem = () => {
    axios.get(route('api.studio.video.draft.edit', [props.slug])).then((response) => {
        categories.value = response.data.categories;
        item.value = response.data.item;
        // if (item.publish_time < Math.floor((new Date().getTime()) / 1000)) {
        //     item.publish_time = Math.floor((new Date().getTime()) / 1000);
        // }
        // item.publish_time = new Date(item.publish_time).getTime() - new Date().getTimezoneOffset() * 60;
        // random key to force re-render
        key.value = Math.random().toString(36).substring(2, 15);
    })
};

function prepareFormData(){
    const formData = useForm({
        title: item.value.title,
        description: item.value.description,
        tags: item.value.tags,
        visibility: item.value.visibility,
        publish_time: item.value.visibility === 'scheduled' ? item.value.publish_time : null,
        audience: item.value.audience,
        platforms: item.value.platforms,
        category_id: item.value.category.id,
    });

    // convert local publish time to unix integer
    // let publish_time = Math.floor(new Date(item.value.publish_time).getTime());
    // formData.append('publish_time', publish_time);
    // item.value.platforms.forEach((platform) => {
    //     formData.append('platforms[]', platform);
    // });
    return formData;
}

const handleSaveDraft = () => {
    axios.patch(route('api.studio.video.draft.update', [item.value.slug]), prepareFormData()).then(() => {
            // router.get(route('studio.content'))
            getItem();
            useToastStore().add({
                'message': 'Draft saved',
                'type': 'success',
            });
        }
    ).catch((error) => {
        form.errors = error.response.data.errors || {};
        useToastStore().add({
            'message': 'Error saving draft',
            'type': 'warning',
        });
    })
};

const handlePublish = () => {
    axios.post(route('api.studio.video.publish', [item.value.slug]), prepareFormData()).then(() => {
            router.get(route('studio.content'))
        }
    ).catch((error) => {
        form.errors = error.response.data.errors || {};
        console.log(error);
    });
};

const handleDelete = () => {
    // axios.delete(route('api.studio.video.draft.delete', [item.value.slug])).then(() => {
    //         router.get(route('studio.content'))
    //     }
    // ).catch((error) => {
    //     form.errors = error.response.data.errors || {};
    //     console.log(error);
    // });
};


</script>

<template>

    <Head :title="headTitle" />


    <div  v-if="useAuthStore().user != null && item != null" :key="key">
        <div class=" flex flex-col md:flex-row ">
            <div class="display:initial  ">
                <div class="flex flex-col sticky top-16 md:h-[calc(100vh-4rem)] w-full md:w-72 p-2 px-5 border border-b-0 border-l-0 border-t-0 border-zinc-200 dark:border-zinc-800 shadow dark:shadow-zinc-800">
                        <Link :href="route('studio.content')" class="mt-2 ">
                            <QuaternaryButton>
                                <font-awesome-icon :icon="['fas', 'arrow-left']" class="w-5 aspect-square "/>
                                <p class="text-sm font-bold my-auto">Channel Content</p>
                            </QuaternaryButton>
                        </Link>
                    <div class="mt-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 w-full aspect-21/12 overflow-hidden">
                        <img v-if="item.thumbnail_path" :src="item.thumbnail_path" class="w-full h-full"/>
                    </div>
                    <p class="mx-1 mt-3 text-sm font-bold" v-text="'Your ' + type"></p>
                    <p class="text-zinc-500 dark:text-zinc-400 mx-1 text-sm" v-text="item.title"></p>
                    <div class="py-3 flex-grow flex flex-col justify-between overflow-hidden rounded w-full  mb-2 md:ml-0 ">
                        <div class="flex flex-col flex-wrap  px-auto gap-y-2 z-10 text-sm font-bold text-center  ">
                            <StudioEditItemButton :currentTab="tab" :tab="'details'" @changePage="tab = 'details'"/>
                            <StudioEditItemButton v-if="props.type !== 'video_draft'" :currentTab="tab" :tab="'analytics'" @changePage="tab = 'analytics'"/>
                            <StudioEditItemButton v-if="props.type !== 'video_draft'" :currentTab="tab" :tab="'comments'" @changePage="tab = 'comments'"/>
                        </div>
                        <div class="flex flex-row flex-wrap gap-2 justify-center">
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
                            <div class="flex justify-center">
                                <TertiaryButton @click="handleDelete" class="h-[3rem]" :class="{ 'opacity-25': form.processing }"
                                                :disabled="form.processing">DELETE
                                </TertiaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-grow">
              <ConsistentPadding class="-mt-4 px-4 max-w-4xl" >
                  <!--<TitleComponent :text="headTitle" class="">-->
                  <!--    <font-awesome-icon :icon="['fas', 'upload']"  class="w-6 h-6 my-auto"/>-->
                  <!--</TitleComponent>-->
                <div class="m t-8 space-y-3 sm:min-w-[20rem] w-full ">
                    <StudioTextInput :value="item.title"
                                     @update:model-value="item.title = $event"
                                     label="Title"
                                     placeholder="Video title"
                                     for="title"
                                     :error_message="form.errors.title ? form.errors.title[0] : null"
                    />
                    <StudioTextInput :value="item.description"
                                     @update:model-value="item.description = $event"
                                     @submit=""
                                     label="Description"
                                     placeholder="Video description"
                                     for="description"
                                     :enter-submit="false"
                                     :maxlength="1000"
                                     :error_message="form.errors.description ? form.errors.description[0] : null"
                    />
                    <StudioImageInput :value="item.thumbnail_path"
                                      :endpoint="route('api.studio.video.draft.thumbnail.update', [item.slug])"
                                      @refresh="getItem"
                                      label="Thumbnail"
                                      for="thumbnail"
                                      :rounded="false"
                                      description="Select or upload a picture that shows what's in your video. A good thumbnail stands out and draws viewers' attention."
                                      placeholder="Channel Profile Banner"
                                      :error_message="form.errors.thumbnail ? form.errors.thumbnail[0] : null"
                    />
                    <StudioTagsInput :value="item.tags"
                                     @update:model-value="item.tags = $event"
                                     label="Tags"
                                     for="tags"
                                     :error_message="form.errors.tags ? form.errors.tags[0] : null"
                    />
                    <StudioMadeForKidsInput :value="item.audience"
                                             @update:model-value="item.audience = $event"
                                             label="Made for kids"
                                             for="made_for_kids"
                                             :error_message="form.errors.audience ? form.errors.audience[0] : null"
                    />

                    <StudioVisibilityInput :value="item.visibility"
                                           :publish_time="item.publish_time"
                                           @update:model-visibility="item.visibility = $event"
                                           @update:model-publish-time="item.publish_time = $event"
                                           :errors="form.errors"
                    />
                    <StudioCategoryInput :value="item.category.id"
                                         :categories="categories"
                                         @update:model-value="item.category.id = $event"
                                         :errors="form.errors"
                     />

                    <StudioPlatformsInput :errors="form.errors"
                                            :platforms="item.platforms"
                                              :preferred_source="item.preferred_source"
                                              @update:model-value="item.platforms = $event;"
                     />

                    <!--<StudioPrimarySourceInput :preferred_source="item.preferred_source"-->
                    <!--                          :sources="['youtube']"-->
                    <!--                          @update:model-value="item.preferred_source = $event"-->
                    <!--                          label="Primary Source"-->
                    <!--                          for="primary_source"-->
                    <!--                          :errors="form.errors"-->
                    <!--/>-->
                    <StudioCheck/>

                </div>
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

