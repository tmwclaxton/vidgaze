<script setup>
import InputError from '@/Components/Inputs/InputError.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import {reactive, ref} from 'vue';
import {useToastStore} from "@/Stores/ToastStore";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = reactive({
    data: {
        current_password: '',
        password: '',
        password_confirmation: '',
        processing: false,
    },
    errors: {},
});

const save = () => {
    axios.patch(route('api.password.change'), {
        current_password: form.data.current_password,
        password: form.data.password,
        password_confirmation: form.data.password_confirmation,
    }).then((response) => {
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType,
        });
        form.errors = {};
    }).catch((errors) => {
        useToastStore().add({
            message: errors.response.data.message,
            type: 'warning',
        });
        form.errors = errors.response.data.errors;

        if (form.errors.password) {
            form.data.password = '';
            passwordInput.value.focus();
        }

        if (form.errors.current_password) {
            form.data.current_password = '';
            currentPasswordInput.value.focus();
        }

    }).finally(() => {
        form.processing = false;
    });
}

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Update Password</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <div class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Current Password" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.data.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="New Password" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.data.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    v-model="form.data.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton @click="save" :disabled="form.data.processing">Save</PrimaryButton>

                <!--<Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">-->
                <!--    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>-->
                <!--</Transition>-->
            </div>
        </div>
    </section>
</template>
