<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import TextInput from "@/Components/Inputs/TextInput.vue";
import FileUpload from "@/Components/Inputs/FileUpload.vue";
import toast from "@/Stores/toast";

const submit = () => {
    form.post("/test");
}

const addToast = () => {
    toast.add({
        message: "Hello from dashboard",
    });
}




</script>

<template>
    <!--    <Head title="Landing" />-->
    <AuthenticatedLayout>
        <div class="-mt-16 "></div>
                <div class=" ">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="  overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">You're logged in!</div>
                            <!--<ClockSVG class="w-5 fill-blue-400"/>-->
                            <div v-if="$page.props.flash.success" class="alert">
                                {{ $page.props.flash.success }}
                            </div>

                            <button @click="addToast">Add toast</button>


                            <p class="font-bold">Buttons Overview</p>
                            <div>
                                <DangerButton>Danger</DangerButton>
                                <PrimaryButton>Primary</PrimaryButton>
                                <SecondaryButton>Secondary</SecondaryButton>
                            </div>

                            <p class="font-bold">Inputs</p>
                            <div class="flex flex-col space-y-2">
                                <Checkbox></Checkbox>
                                <InputLabel>Input Label</InputLabel>
                                <TextInput model-value="test"></TextInput>
                                <InputError message="this is an error">Input Error</InputError>
                            </div>

                            <p class="font-bold">File upload</p>
                            <div class="flex flex-col space-y-2">
                                <FileUpload></FileUpload>
                            </div>


                        </div>
                    </div>
                </div>


    </AuthenticatedLayout>
</template>
