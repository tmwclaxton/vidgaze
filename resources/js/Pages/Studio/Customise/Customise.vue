<script setup>

import {useAuthStore} from "@/Stores/AuthStore";
import {ref} from "vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import TitleComponent from "@/Components/General/TitleComponent.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import StudioTextInput from "@/Pages/Studio/Customise/Partials/StudioTextInput.vue";
import {useToastStore} from "@/Stores/ToastStore";




const updateChannelDetails = () => {
    axios.patch(route('api.creator.update',useAuthStore().user.creator.slug),{
        name: useAuthStore().user.creator.name,
        bio: useAuthStore().user.creator.bio,
        contact_email: useAuthStore().user.creator.contact_email,
    }).then(response => {
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType
        });
    }).catch(error => {
        useToastStore().add({
            message: error.response.data.message,
            type: "warning"
        });
    });
}

</script>

<template>
    <div  v-if="useAuthStore().user != null">
            <div class=" flex flex-col sm:flex-row-reverse ">
                <div class="display:initial  mt-8 mr-6 ">
                    <div class="sticky top-5 w-full sm:w-56">
                        <consistent-content-holder>
                            <div class="pb-3 overflow-hidden rounded w-full  mb-2 sm:ml-0 ">
                                <div class="mx-12 my-4  flex justify-center">
                                    <img class="w-1/2 sm:w-full aspect-square rounded-full "
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
                            <quaternary-button class="m-2">
                                Save
                            </quaternary-button>
                        </consistent-content-holder>
                    </div>
                </div>


                <consistent-padding class=" sm:mr-4 flex-grow h-full">
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
                        />

                        <!--channel bio-->
                        <StudioTextInput :value="useAuthStore().user.creator.bio"
                                        @update:model-value="useAuthStore().user.creator.bio = $event"
                                        @submit="updateChannelDetails()"
                                        label="Channel bio"
                                        placeholder="Channel bio"
                                        for="channel-bio"
                                        :enter-submit="false"
                                         maxlength="1000"
                        />

                        <!--channel profile image-->

                        <!--channel banner image-->

                        <!--channel contact email-->
                        <StudioTextInput :value="useAuthStore().user.creator.contact_email"
                                         @update:model-value="useAuthStore().user.creator.contact_email = $event"
                                         @submit="updateChannelDetails()"
                                         label="Contact Email"
                                         placeholder="Contact Email"
                                         for="contact-email"
                                         maxlength="320"
                        />

                    </div>


                </consistent-padding>
            </div>
        </div>
</template>
