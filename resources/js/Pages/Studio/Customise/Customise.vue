<script setup>

import {useAuthStore} from "@/Stores/AuthStore";
import {ref} from "vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import StudioTextInput from "@/Pages/Studio/Partials/StudioTextInput.vue";
import {useToastStore} from "@/Stores/ToastStore";
import StudioImageInput from "@/Pages/Studio/Partials/StudioImageInput.vue";

const name = 'Customise';
const errors = ref({});

const updateChannelDetails = () => {
    axios.patch(route('api.creator.update'),{
        name: useAuthStore().user.creator.name,
        bio: useAuthStore().user.creator.bio,
        contact_email: useAuthStore().user.creator.contact_email,
    }).then(response => {
        errors.value = {};
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType
        });
    }).catch(error => {
        errors.value = error.response.data.errors;
        useToastStore().add({
            message: "There was an error updating your channel details.",
            type: "warning"
        });
    });
}
</script>

<template>
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
                    <consistent-padding class=" flex-grow h-full">
                        <TitleComponent :text="'Customise your channel'" class="  mb-8">
                            <font-awesome-icon icon="user-cog" class="w-6 h-6 my-auto"/>
                        </TitleComponent>
                        <div class="flex flex-col gap-y-3">
                            <!--channel name-->
                            <!--<TextInput v-model="name" label="Channel name" class="mb-4" placeholder="Channel name"/>-->
                            <StudioTextInput :value="useAuthStore().user.creator.name"
                                             @update:model-value="useAuthStore().user.creator.name = $event"
                                             @submit="updateChannelDetails()"
                                             label="Channel Name"
                                             placeholder="Channel Name"
                                             for="channel-name"
                                             :error_message="errors.name ? errors.name[0] : null"

                            />

                            <!--channel bio-->
                            <StudioTextInput :value="useAuthStore().user.creator.bio"
                                             @update:model-value="useAuthStore().user.creator.bio = $event"
                                             @submit="updateChannelDetails()"
                                             label="Channel bio"
                                             placeholder="Channel bio"
                                             for="channel-bio"
                                             :enter-submit="false"
                                             :maxlength="1000"
                                             :error_message="errors.bio ? errors.bio[0] : null"

                            />

                            <!--channel profile image-->
                            <StudioImageInput :value="useAuthStore().user.creator.avatar_url"
                                              :endpoint="route('api.creator.update.avatar')"
                                              @refresh="useAuthStore().getUser();"
                                              label="Channel Profile Image"
                                              description="Your profile picture will appear where your channel is presented on VidGaze, like next to your videos and comments."
                                              recommendation="We recommend using a square image (1:1 aspect ratio) with a minimum resolution of 98x98 pixels and a maximum file size of 4MB."
                                              placeholder="Channel Profile Image"
                                              for="channel-profile-image"
                                              :rounded="true"
                            />

                            <!--channel banner image-->
                            <StudioImageInput :value="useAuthStore().user.creator.banner_url"
                                              :endpoint="route('api.creator.update.banner')"
                                              @refresh="useAuthStore().getUser();"
                                              label="Channel Profile Banner"
                                              description="Your banner will appear at the top of your channel page."
                                              recommendation="We recommend using a 16:9 aspect ratio image with a minimum resolution of 2048x1152 pixels and a maximum file size of 6MB."
                                              placeholder="Channel Profile Banner"
                                              for="channel-profile-banner"
                                              :rounded="false"
                            />
                            <!--channel contact email-->
                            <StudioTextInput :value="useAuthStore().user.creator.contact_email"
                                             @update:model-value="useAuthStore().user.creator.contact_email = $event"
                                             @submit="updateChannelDetails()"
                                             label="Contact Email"
                                             placeholder="Contact Email"
                                             for="contact-email"
                                             :maxlength="320"
                                             :error_message="errors.contact_email ? errors.contact_email[0] : null"
                            />

                        </div>


                    </consistent-padding>
                </div>
            </div>
        </div>
</template>
