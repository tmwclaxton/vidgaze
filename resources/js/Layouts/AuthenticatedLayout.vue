<script setup>
import { ref } from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
import CookieConsent from "@/Components/Popup/CookieConsent.vue";

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

        <div class="">

            <Nav
                :showingNavigationDropdown="showingNavigationDropdown"
                 :showingStudioLinks="showingStudioLinks"
                @toggleSidenav="showingNavigationDropdown = !showingNavigationDropdown"
                @toggleSidenavOff="showingNavigationDropdown = false"
            />

            <!-- Page Content -->
            <main class="flex flex-row" >

                <div class="pointer-events-none opacity-0  transition duration-700 ease-in-out"  :class="{'sm:w-52 sm:m r-7': showingNavigationDropdown}">

                </div>
                <div class="flex-grow  transition duration-700 ease-in-out">
                    <slot  />
                </div>
                <!--<CookieConsent/>-->


            </main>

        </div>
    </div>
</template>
