<script setup>
import { ref } from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";

let showingNavigationDropdown = ref(false);

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: false,
        default: false
    },
});
</script>

<template>



    <div>
        <!-- this is where the toast message popup is added -->
        <ToastList :flash="$page.props.flash"/>

        <div class=" flex flex-col   ">

            <Nav
                :showingNavigationDropdown="showingNavigationDropdown"
                 :showingStudioLinks="showingStudioLinks"
                @toggleSidenav="showingNavigationDropdown = !showingNavigationDropdown"
                @toggleSidenavOff="showingNavigationDropdown = false"
            />

            <!-- Page Content -->
            <main class="flex flex-row flex-grow    " >

                <div v-if="!route().current('about')" class="pointer-events-none opacity-0 flex-shrink-0 transition  ease-in-out"  :class="{'sm:w-64  ': showingNavigationDropdown, 'sm:w-24': !showingNavigationDropdown}">

                </div>
                <div class="flex-shrink transition duration-700 ease-in-out w-full"  :class="{'   ': showingNavigationDropdown}">
                    <slot  />
                </div>
                <!--<CookieConsent/>-->


            </main>

        </div>
    </div>
</template>
