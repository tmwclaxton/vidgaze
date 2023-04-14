<script setup>

import {onMounted, onUnmounted, ref, watch} from "vue";
import TopNavBar from "@/Shared/Navigation/TopNavBar.vue";
import SideBar from "@/Shared/Navigation/SideBar.vue";

//open and close the side bar
const expandedSearchBar = ref(false); //this is for mobile search bar when you click the search icon it hides the hamburger and stuff
const expandedSearchResults = ref(false);

const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: true
    },
    showingNavigationDropdown: {
        type: Boolean,
        required: true
    },
});

const emits = defineEmits(['toggleSidenav', 'toggleSidenavOff']);

// this is for the search bar so when you resize the window it will close the expanded search bar
const windowWidth = ref(window.innerWidth)
const handleResize = () => {
    windowWidth.value = window.innerWidth;
    // console.log(windowWidth.value);
    if (windowWidth.value > 640) {
        expandedSearchBar.value = false;

    } else {
        // it makes sense to close sidenav when you resize the window to mobile cause otherwise it is a bit annoying
        emits('toggleSidenavOff');

        // check if the search results are expanded if so then expand the search bar for mobile
        if (expandedSearchResults.value) {
            expandedSearchBar.value = true;
        } else {
            expandedSearchBar.value = false;
        }
        //if sidebar is open and search results are expanded then close the sidebar
        if (props.showingNavigationDropdown && expandedSearchResults.value) {
            console.log('close sidebar');
            emits('toggleSidenavOff');
        }
    }
}

const toggleExpandedSearchBarOn  = () => {
    if (windowWidth.value <= 640) {
        expandedSearchBar.value = true;
        emits('toggleSidenavOff');
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
    // showingNavigationDropdown.value = !showingNavigationDropdown.value;
    emits('toggleSidenav');
}

// this is for mobile so when you click the profile / upload / notification icon it will close the sidenav
const toggleShowingNavigationDropdownOff = () => {
    if (windowWidth.value <= 640) {
        emits('toggleSidenavOff');
    }
}

const toggleExpandedSearchResultsOn = () => {
    expandedSearchResults.value = true;
}
const toggleExpandedSearchResultsOff = () => {
    expandedSearchResults.value = false;
    toggleExpandedSearchBarOff()
}

</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class=" fixed z-40 top-0 w-full pointer-events-none">
        <!-- Primary Navigation Menu -->

            <TopNavBar @toggleSidenav="toggleShowingNavigationDropdown()"
                       @toggleSidenavOff="toggleShowingNavigationDropdownOff()"
                       @toggleExpandedSearchBarOn="toggleExpandedSearchBarOn()"
                       @toggleExpandedSearchBarOff="toggleExpandedSearchBarOff()"
                       @toggleExpandedSearchResultsOn="toggleExpandedSearchResultsOn()"
                       @toggleExpandedSearchResultsOff="toggleExpandedSearchResultsOff()"
                       :showingNavigationDropdown="showingNavigationDropdown"
                       :expandedSearchBar="expandedSearchBar"
                       :showingStudioLinks="showingStudioLinks"
                       :expandedSearchResults="expandedSearchResults"/>

            <SideBar :showingNavigationDropdown="showingNavigationDropdown" :showingStudioLinks="showingStudioLinks"/>

    </nav>

</template>

