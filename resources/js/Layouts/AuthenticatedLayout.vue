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

            <Nav :showingNavigationDropdown="showingNavigationDropdown" :showingStudioLinks="showingStudioLinks"/>

            <!-- Page Content -->
            <main class="" >

                <slot />
                <!--<CookieConsent/>-->


            </main>

        </div>
    </div>
</template>
