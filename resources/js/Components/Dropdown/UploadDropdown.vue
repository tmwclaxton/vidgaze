<script setup>
import DropdownLink from '@/Components/Links/DropdownLink.vue';
import Dropdown from '@/Components/Dropdown/Dropdown.vue';
import {Link} from '@inertiajs/vue3';


import CloudIcon from '~/images/icons/cloud_upload.svg';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

</script>


<template>

    <div class="relative hidden sm:flex">
        <Dropdown align="right" width="56" distance="1.5">
            <template #trigger>
                <span class="inline-flex rounded-md  ">
                    <button type="button" class=" inline-flex items-center h-full  transition ease-in-out duration-150">
                        <CloudIcon class="w-6 aspect-square rounded-full text-white aspect-square  "/>
                    </button>
                </span>
            </template>

            <template #content>
                <div class="flex flex-col gap-y-1 block w-full px-2 py-1 text-left text-sm ">
                    <DropdownLink :href="route('studio.upload')" class="flex flex-row space-x-2">
                        <font-awesome-icon :icon="['fas', 'upload']" class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Multiupload</span>
                    </DropdownLink>
                    <DropdownLink :href="route('studio.content')" class="flex flex-row space-x-2">
                        <font-awesome-icon :icon="['fas', 'video']" class="w-5 h-5 flex-shrink-0"/>
                        <span class="font-bold">Manage content</span>
                    </DropdownLink>
                </div>
            </template>
        </Dropdown>
    </div>
</template>

