<script setup>
import PaddingLayout from '@/Layouts/Partials/ConsistentPadding.vue';
import {useAuthStore} from "@/Stores/AuthStore";
import {Link} from "@inertiajs/inertia-vue3";
import {useToastStore} from "@/Stores/ToastStore";
import {ref} from "vue";
const toastStore = useToastStore();
const authStore = useAuthStore();

const slug = ref('');
const role = ref('moderator');

const changeUserRole = () => {
    axios.post(route('api.admin.change_user_role'), {creator_slug: slug.value, role: role.value}).then(response => {
        toastStore.add({
            message: 'User role changed successfully.',
            type: 'success',
        });
    }).catch(error => {
        toastStore.add({
            message: 'Sorry we couldn\'t change the user role!  Please try again.',
            type: 'warning',
        });
    });
}

</script>
<template>

    <PaddingLayout>
        <!--    <Head title="Landing" />-->
        <div class="h1 text dark:textDark">


            <div class="  px-2 sm:rounded-lg w-full flex flex-col align-middle justify-center justify-items-center">

                <!--Welcome message + name-->
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-200 mb-5 text-center select-none">Welcome back, {{ authStore.user.creator.name }}!</h1>

                <!--Horizon and Telescope links-->
                <div class=" mx-auto max-w-md lg:max-w-screen-xl mb-5 select-none">

                    <!-- Change User Role, input field for user slug and select field for role -->
                    <div class=" p-4 rounded-lg w-full flex flex-col gap-2 shadow-xl">
                            <div class="uppercase text-sm text-zinc-400">
                                Change User Role
                            </div>
                            <div class="flex space-x-2 items-center">
                                <input v-model="slug"
                                    type="text" class="w-40 p-2 border border-zinc-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="User Slug">
                                <select v-model="role"
                                    class="w-40 p-2 border border-zinc-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button @click="changeUserRole('creator-slug', 'role')"
                                    class="w-40 p-2 bg-blue-500 text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-blue-600 dark:focus:ring-blue-600 dark:focus:border-blue-600">
                                Change Role
                            </button>
                    </div>

                    <!-- Moderator Actions record -->






                </div>


                <!--&lt;!&ndash;Statistics overview&ndash;&gt;-->
                <!--<div class="flex flex-col md:flex-row space-x-8 mx-10">-->
                <!--    <div class="shadow-md p-4">-->
                <!--            <div class="flex flex-col">-->
                <!--                <div class="flex space-x-8 w-56">-->
                <!--                    <div class="">-->
                <!--                        <div class="uppercase text-sm text-zinc-400">-->
                <!--                            New users-->
                <!--                        </div>-->
                <!--                        <div class="mt-1">-->
                <!--                            <div class="flex space-x-2 items-center">-->
                <!--                                <div class="text-2xl">-->
                <!--                                    35-->
                <!--                                </div>-->
                <!--                                <div class="text-xs text-green-800 bg-green-200 rounded-md p-1">-->
                <!--                                    +4.5%-->
                <!--                                </div>-->
                <!--                            </div>-->
                <!--                        </div>-->
                <!--                    </div>-->
                <!--                    <div class="">-->
                <!--                        <svg class="h-16 w-20 text-zinc-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">-->
                <!--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />-->
                <!--                        </svg>-->
                <!--                    </div>-->
                <!--                </div>-->
                <!--        </div>-->
                <!--    </div>-->


                <!--    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">-->
                <!--        <div class="flex items-center justify-between pb-4 bg-white dark:bg-zinc-900">-->
                <!--            <div>-->
                <!--                <button id="dropdownActionButton" data-dropdown-toggle="dropdownAction" class="inline-flex items-center text-zinc-500 bg-white border border-zinc-300 focus:outline-none hover:bg-zinc-100 focus:ring-4 focus:ring-zinc-200 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-600 dark:hover:bg-zinc-700 dark:hover:border-zinc-600 dark:focus:ring-zinc-700" type="button">-->
                <!--                    <span class="sr-only">Action button</span>-->
                <!--                    Action-->
                <!--                    <svg class="w-3 h-3 ml-2" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>-->
                <!--                </button>-->

                <!--            </div>-->
                <!--            <label for="table-search" class="sr-only">Search</label>-->
                <!--            <div class="relative">-->
                <!--                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">-->
                <!--                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>-->
                <!--                </div>-->
                <!--                <input type="text" id="table-search-users" class="block p-2 pl-10 text-sm text-zinc-900 border border-zinc-300 rounded-lg w-80 bg-zinc-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search for users">-->
                <!--            </div>-->
                <!--        </div>-->
                <!--        <table class=" text-sm text-left text-zinc-500 dark:text-zinc-400">-->
                <!--            <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-700 dark:text-zinc-400">-->
                <!--            <tr>-->
                <!--                <th scope="col" class="p-4">-->
                <!--                    <div class="flex items-center">-->
                <!--                        <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-800 dark:focus:ring-offset-zinc-800 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">-->
                <!--                        <label for="checkbox-all-search" class="sr-only">checkbox</label>-->
                <!--                    </div>-->
                <!--                </th>-->
                <!--                <th scope="col" class="px-6 py-3">-->
                <!--                    Name-->
                <!--                </th>-->
                <!--                <th scope="col" class="px-6 py-3">-->
                <!--                    Position-->
                <!--                </th>-->
                <!--                <th scope="col" class="px-6 py-3">-->
                <!--                    Status-->
                <!--                </th>-->
                <!--                <th scope="col" class="px-6 py-3">-->
                <!--                    Action-->
                <!--                </th>-->
                <!--            </tr>-->
                <!--            </thead>-->
                <!--            <tbody>-->
                <!--            <tr class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-600">-->
                <!--                <td class="w-4 p-4">-->
                <!--                    <div class="flex items-center">-->
                <!--                        <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-800 dark:focus:ring-offset-zinc-800 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">-->
                <!--                        <label for="checkbox-table-search-1" class="sr-only">checkbox</label>-->
                <!--                    </div>-->
                <!--                </td>-->
                <!--                <th scope="row" class="flex items-center px-6 py-4 text-zinc-900 whitespace-nowrap dark:text-white">-->
                <!--                    <img class="w-10 h-10 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-1.jpg" alt="Jese image">-->
                <!--                    <div class="pl-3">-->
                <!--                        <div class="text-base font-semibold">Neil Sims</div>-->
                <!--                        <div class="font-normal text-zinc-500">neil.sims@flowbite.com</div>-->
                <!--                    </div>-->
                <!--                </th>-->
                <!--                <td class="px-6 py-4">-->
                <!--                    React Developer-->
                <!--                </td>-->
                <!--                <td class="px-6 py-4">-->
                <!--                    <div class="flex items-center">-->
                <!--                        <div class="h-2.5 w-2.5 rounded-full bg-green-500 mr-2"></div> Online-->
                <!--                    </div>-->
                <!--                </td>-->
                <!--                <td class="px-6 py-4">-->
                <!--                    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit user</a>-->
                <!--                </td>-->
                <!--            </tr>-->

                <!--            </tbody>-->
                <!--        </table>-->
                <!--    </div>-->

                <!--</div>-->

            </div>
        </div>
    </PaddingLayout>

</template>
