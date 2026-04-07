<script setup>

import {computed, onBeforeMount, onMounted, ref, watch} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
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
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import Badge from "@/Components/General/Badge.vue";

let props = defineProps({
    slug: String,
    type: String,
});

const headTitle = computed(() => {
    if (props.type === 'video_draft') {
        return 'Upload Video';
    }
    if (props.type === 'video') {
        return 'Edit Video';
    }
    if (props.type === 'stream') {
        return 'Edit Stream';
    }
    return 'Studio';
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
const categories = ref([]);
const uploadable_platforms = ref([]);
const key = ref(0);
let form = useForm({});
const forceRerender = () => {
    key.value += 1;
};

onMounted(() => {
    getItem();
});

const item = ref(null);
const item_original = ref(null);
const thumbnailDifferent = ref(false);
const itemChanged = computed(() => {
    if (item.value === null || item_original.value === null) {
        return false;
    }
    return (JSON.stringify(item.value) !== JSON.stringify(item_original.value)) || thumbnailDifferent.value;
});


const getItem = () => {
    form.errors = {};
    if (props.type === 'video_draft') {
        axios.get(route('api.studio.video.draft.edit', [props.slug])).then((response) => {
            categories.value = response.data.categories;
            uploadable_platforms.value = response.data.platforms;
            item.value = response.data.item;
            // for some reason item and item_original seem to be referencing the same object but I don't want that
            item_original.value = JSON.parse(JSON.stringify(item.value));
            forceRerender();
        })
    }
};

function prepareFormData(){
    // add 1 hour to publish time, I don't think this is a timezone issue I think the date component I'm using is just broken
    // as the date shown in the input is correct but the value is 1 hour behind
    let publish_time = new Date(item.value.publish_time);
    publish_time.setHours(publish_time.getHours() + 1);
    item.value.publish_time = publish_time.toISOString().slice(0, 19).replace('T', ' ');

    const formData = useForm({
        title: item.value.title,
        description: item.value.description,
        tags: item.value.tags,
        visibility: item.value.visibility,
        publish_time: item.value.visibility === 'scheduled' ? item.value.publish_time : null,
        audience: item.value.audience,
        platforms: item.value.platforms,
        category_id: item.value.category.id,
        preferred_source: item.value.platforms.length > 0 ? item.value.preferred_source: null,
        language: item.value.language,
        region: item.value.region,
    });
    return formData;
}

const triggerSave = ref(false);
const handleSaveDraft = () => {
    if (thumbnailDifferent.value) {
        triggerSave.value = true;
    }
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
    }).finally(() => {
        triggerSave.value = false;
    });
};

const handlePublish = () => {
    axios.post(route('api.studio.video.draft.publish', [item.value.slug]), prepareFormData()).then(() => {
            router.get(route('studio.content'))
            useToastStore().add({
                'message': 'Published',
                'type': 'success',
            });
        }
    ).catch((error) => {
        form.errors = error.response.data.errors || {};
        console.log(error);
    });
};

const handleDelete = () => {
    useConfirmModalStore().buttonOneText = 'Cancel';
    useConfirmModalStore().buttonTwoText = 'Delete';
    useConfirmModalStore().title = 'Are you sure, this will delete your ' + type.value + ' permanently?';
    useConfirmModalStore().show = true;
    useConfirmModalStore().continue = () => {
        axios.delete(route('api.studio.video.draft.delete', [item.value.slug])).then(() => {
                router.get(route('studio.content'))
            }
        ).catch((error) => {
            form.errors = error.response.data.errors || {};
            console.log(error);
        });
    };
};



</script>

<template>

    <SeoHead
        :title="headTitle"
        description="Manage your video or stream details in VidGaze Studio."
        noindex
    />


    <div  v-if="useAuthStore().user != null && item != null" >
        <div class=" flex flex-col md:flex-row ">
            <div class="display:initial  ">
                <div class="flex flex-col sticky top-16 md:h-[calc(100vh-4rem)] overflow-hidden w-full md:w-72 p-2 px-5 border border-b-0 border-l-0 border-t-0 border-zinc-200 dark:border-zinc-800 shadow dark:shadow-zinc-800">
                    <Link :href="route('studio.content')" class="mt-2 ">
                        <QuaternaryButton>
                            <font-awesome-icon :icon="['fas', 'arrow-left']" class="w-5 aspect-square "/>
                            <p class="text-sm font-bold my-auto">Channel Content</p>
                        </QuaternaryButton>
                    </Link>
                    <div class="mt-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 w-full aspect-21/12 overflow-hidden">
                        <img v-if="item.thumbnail_path" :src="item.thumbnail_path" class="w-full h-full"/>
                    </div>
                    <p class="mx-1 mt-3 text-sm font-bold break-all w-full" v-text="'Your ' + type"></p>
                    <p class="text-zinc-500 dark:text-zinc-400 mx-1 text-sm break-all" v-text="item.title"></p>
                    <div class="py-3 flex-grow flex flex-col gap-y-3 justify-between overflow-hidden rounded w-full  mb-2 md:ml-0 ">
                        <div class="flex flex-col flex-wrap  px-auto gap-y-2 z-10 text-sm font-bold text-center  ">
                            <StudioEditItemButton :currentTab="tab" :tab="'details'" @changePage="tab = 'details'"/>
                            <StudioEditItemButton v-if="props.type !== 'video_draft'" :currentTab="tab" :tab="'analytics'" @changePage="tab = 'analytics'"/>
                            <StudioEditItemButton v-if="props.type !== 'video_draft'" :currentTab="tab" :tab="'comments'" @changePage="tab = 'comments'"/>
                        </div>
                        <div class="flex flex-col gap-2">
                            <Badge v-if="itemChanged"  :key="key" extra-classes="mx-auto text-lg text-red-500 dark:text-red-500 text-center font-bold mb-3"
                                   text="">
                                Unsaved changes
                            </Badge>
                            <div class="flex flex-row flex-wrap gap-2 justify-center">
                                <div class="flex justify-center">
                                    <TertiaryButton @click="handleSaveDraft" class="h-[3rem]"
                                                    :class="{ 'opacity-25': form.processing }"
                                                    :disabled="form.processing">SAVE DRAFT
                                    </TertiaryButton>
                                </div>
                                <div class="flex justify-center">
                                    <TertiaryButton @click="handlePublish" class="h-[3rem]"
                                                    :class="{ 'opacity-25': form.processing }"
                                                    :disabled="form.processing || itemChanged">
                                        {{ form.visibility === 'scheduled' ? 'SCHEDULE' : 'PUBLISH' }}
                                    </TertiaryButton>
                                </div>
                                <div class="flex justify-center">
                                    <TertiaryButton @click="handleDelete" class="h-[3rem]"
                                                    :class="{ 'opacity-25': form.processing }"
                                                    :disabled="form.processing">DELETE
                                    </TertiaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-grow ">
              <ConsistentPadding class="-mt-4 px-4 flex flex-col-reverse xl:flex-row gap-3 pr-6 " >
                  <!--<TitleComponent :text="headTitle" class="">-->
                  <!--    <font-awesome-icon :icon="['fas', 'upload']"  class="w-6 h-6 my-auto"/>-->
                  <!--</TitleComponent>-->
                <div class="m t-8 space-y-3 sm:min-w-[20rem] w-full flex-grow-1 max-w-4xl">
                    <StudioTextInput :value="item.title"
                                     @update:model-value="item.title = $event"
                                     label="Title"
                                     placeholder="Video title"
                                     for="title"
                                     :maxlength="100"
                                     :error_message="form.errors.title ? form.errors.title[0] : null"
                    />
                    <StudioTextInput :value="item.description"
                                     @update:model-value="item.description = $event"
                                     @submit=""
                                     label="Description"
                                     placeholder="Video description"
                                     for="description"
                                     :enter-submit="false"
                                     :maxlength="5000"
                                     :error_message="form.errors.description ? form.errors.description[0] : null"
                    />
                    <StudioImageInput :value="item.thumbnail_path"
                                      :endpoint="route('api.studio.video.draft.thumbnail.update', [item.slug])"
                                      @refresh="getItem"
                                      @update:thumbnail-different="thumbnailDifferent = $event"
                                      :triggerSave="triggerSave"
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
                                          :uploadable_platforms="uploadable_platforms"
                                            :platforms="item.platforms"
                                              :preferred_source="item.preferred_source"
                                              @update:model-value="item.platforms = $event;"
                                          :error_message="form.errors.platforms ? form.errors.platforms[0] : null"
                     />

                    <StudioPrimarySourceInput v-if="item.platforms.length > 0 && uploadable_platforms.length > 0"
                                              :uploadable_platforms="uploadable_platforms"
                                                :preferred_source="item.preferred_source"
                                              :platforms="item.platforms"
                                              @update:model-value="item.preferred_source = $event"
                                              label="Primary Source"
                                              for="primary_source"
                                              :error_message="form.errors.preferred_source ? form.errors.preferred_source[0] : null"
                    />
                    <StudioCheck/>

                </div>
                  <div class="flex flex-grow ">
                        <video v-if="item" class=" mx-auto h-full max-h-72 rounded-lg" controls>
                            <source :src="item.video_path" type="video/mp4">
                        </video>
                  </div>
            </ConsistentPadding>
            </div>
        </div>
    </div>
</template>

