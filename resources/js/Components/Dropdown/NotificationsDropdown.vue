<script setup>
import Dropdown from '@/Components/Dropdown/Dropdown.vue';

import BellIcon from '~/images/icons/bell.svg';

</script>


<template>
    <div class="relative  flex">
        <Dropdown align="right" width="56" distance="1.5">
            <template #trigger>
                <span class="inline-flex rounded-md  ">
                    <button
                        type="button"
                        class=" inline-flex items-center h-full  transition ease-in-out duration-150"
                    >
                        <BellIcon class="w-6 aspect-square rounded-full text-white aspect-square  "/>
                    </button>
                </span>
            </template>

            <template #content>
                <div class="flex flex-row space-x-2 block w-full px-4 py-2 text-left text-sm ">
                    <!--Title-->
                    <p class="font-bold ">Notifications</p>
                </div>
            </template>
        </Dropdown>
    </div>

</template>

