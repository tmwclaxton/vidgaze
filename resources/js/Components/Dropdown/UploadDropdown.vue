<script setup>
import DropdownLink from '@/Components/Dropdown/DropdownLink.vue';
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
                                            <button
                                                type="button"
                                                class=" inline-flex items-center h-full  transition ease-in-out duration-150"
                                            >
                                                <CloudIcon class="w-6 aspect-square rounded-full text-white aspect-square  "/>
                                            </button>
                                        </span>
            </template>

            <template #content>
                <div class="flex flex-row space-x-2 block w-full px-4 py-2 text-left text-sm ">
                    <!--Title-->
                    <p class="font-bold ">Upload dropdown</p>
                </div>
            </template>
        </Dropdown>
    </div>
</template>

