<script setup>

import {onMounted, onUnmounted, ref, watch} from "vue";
import TopNavBar from "@/Shared/Navigation/TopNavBar.vue";
import SideBar from "@/Shared/Navigation/SideBar.vue";

//open and close the side bar
const showingNavigationDropdown = ref(false);
const expandedSearchBar = ref(false); //this is for mobile search bar when you click the search icon it hides the hamburger and stuff
const expandedSearchResults = ref(false);

const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: true
    }
});


// this is for the search bar so when you resize the window it will close the expanded search bar
const windowWidth = ref(window.innerWidth)
const handleResize = () => {
    windowWidth.value = window.innerWidth;
    // console.log(windowWidth.value);
    if (windowWidth.value > 640) {
        expandedSearchBar.value = false;
    } else {
        // check if the search results are expanded if so then expand the search bar for mobile
        if (expandedSearchResults.value) {
            expandedSearchBar.value = true;
        } else {
            expandedSearchBar.value = false;
        }
        //if sidebar is open and search results are expanded then close the sidebar
        if (showingNavigationDropdown.value && expandedSearchResults.value) {
            showingNavigationDropdown.value = false;
        }
    }
}

const toggleExpandedSearchBarOn  = () => {
    if (windowWidth.value <= 640) {
        expandedSearchBar.value = true;
        showingNavigationDropdown.value = false;
        expandedSearchResults.value = true;
    } else {
        expandedSearchBar.value = false;
    }
}

const toggleExpandedSearchBarOff = () => {
    expandedSearchBar.value = false;
    expandedSearchResults.value = false;
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
})



const toggleShowingNavigationDropdown = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
}
const toggleExpandedSearchResults = () => {
    expandedSearchResults.value = !expandedSearchResults.value;
}

</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class=" fixed z-40 top-0 w-full pointer-events-none">
        <!-- Primary Navigation Menu -->

            <TopNavBar @toggleDropdown="toggleShowingNavigationDropdown()"
                       @toggleExpandedSearchBarOn="toggleExpandedSearchBarOn()"
                       @toggleExpandedSearchBarOff="toggleExpandedSearchBarOff()"
                       @toggleExpandedSearchResults="toggleExpandedSearchResults()"
                       :showingNavigationDropdown="showingNavigationDropdown"
                       :expandedSearchBar="expandedSearchBar"
                       :showingStudioLinks="showingStudioLinks"
                       :expandedSearchResults="expandedSearchResults"/>

            <SideBar :showingNavigationDropdown="showingNavigationDropdown" :showingStudioLinks="showingStudioLinks"/>

    </nav>

</template>

