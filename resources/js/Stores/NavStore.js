import { defineStore } from 'pinia'
import {ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import {runInContext as routes} from "lodash";
export const useNavStore = defineStore('NavStore', {
    state: () => {
        return {
            // showingStudioLinks: false,
            showingBottomNavLinks: true,
            showingNavigationDropdown: false,
            expandedSearchBar: false, //this is for mobile search bar when you click the search icon it hides the hamburger and stuff
            expandedSearchResults: false,
            height: 0,
            width: 0,

        }
    },
    getters: {
        showingStudioLinks() {
            return usePage().props.layoutDisplay === 'studio'
        }
    },
    actions: {
        getExpandedSearchBar() {
            return this.expandedSearchBar;
        },
        getNavigationDropdown() {
            return this.showingNavigationDropdown;
        },
        getExpandedSearchResults() {
            return this.expandedSearchResults;
        },
        getBottomNavLinks() {
            return this.showingBottomNavLinks;
        },

        // there is an event listener for this in nav.vue
        handleResize() {
            this.height = window.innerHeight;
            this.width = window.innerWidth;
            if (window.innerWidth > 640) {
                this.expandedSearchBar = false
            } else {
                // it makes sense to close sidenav when you resize the window to mobile cause otherwise it is a bit annoying
                this.showingNavigationDropdown = false

                // check if the search results are expanded if so then expand the search bar for mobile
                if (this.expandedSearchResults) {
                    this.expandedSearchBar = true
                } else {
                    this.expandedSearchBar = false
                }
                //if sidebar is open and search results are expanded then close the sidebar
                if (this.showingNavigationDropdown && this.expandedSearchResults) {
                    this.showingNavigationDropdown = false
                }
            }

            //check bottom nav links
            this.checkBottomNavLinks()

        },

        checkBottomNavLinks() {
            if (window.innerHeight > 850 || this.showingNavigationDropdown || (useAuthStore().user === null && window.innerHeight > 665)) {
                this.showingBottomNavLinks = true
            } else {
                this.showingBottomNavLinks = false
            }
        },

        toggleShowingNavigationDropdown() {
            this.showingNavigationDropdown = !this.showingNavigationDropdown;
            this.checkBottomNavLinks()
        },

        // this is for mobile so when you click the profile / upload / notification icon it will close the sidenav
        toggleShowingNavigationDropdownOff() {
            if (window.innerWidth <= 640) {
                this.showingNavigationDropdown = false;
            }
        },

        toggleExpandedSearchBarOn() {
            if (window.innerWidth <= 640) {
                this.expandedSearchBar = true
                this.showingNavigationDropdown = false
                this.expandedSearchResults = true
            } else {
                this.expandedSearchBar = false
            }
        },
        toggleExpandedSearchBarOff() {
            this.expandedSearchBar = false;
            this.expandedSearchResults = false;
        },
        toggleExpandedSearchResultsOn() {
            this.expandedSearchResults = true
        },
        toggleExpandedSearchResultsOff() {
            this.expandedSearchResults = false;
            this.toggleExpandedSearchBarOff();
        },
    }
})
