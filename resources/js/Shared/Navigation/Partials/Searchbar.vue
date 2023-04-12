<script setup>
import SearchIcon from '~/images/icons/search.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import {Link} from "@inertiajs/inertia-vue3";
import {defineProps, defineEmits} from "vue";
//name of the component
const name = 'Searchbar';

//accept props
const props = defineProps({

    expandedSearchBar: {
        type: Boolean,
        required: true
    },
    expandedSearchResults: {
        type: Boolean,
        required: true
    },
});

//define emits
const emits = defineEmits(['toggleExpandedSearchBarOff', 'toggleExpandedSearchResultsOff']);

const onClickAway = (event) => {
    if (props.expandedSearchResults) {
        emits('toggleExpandedSearchResultsOff')
    }
}
</script>


<template>

    <!--Search bar-->
    <div v-click-away="onClickAway"
         class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
        <div
            class="relative flex flex-row space-x-3 w-full justify-end sm:justify-center">

            <div class="p-2 pl-1  " :class="{
                                            'hidden': !expandedSearchBar,
                                            ' flex': expandedSearchBar,
                                        }">
                <!--Exit expanded search-->
                <CloseNavSVG @click="$emit('toggleExpandedSearchBarOff')"
                             class="ml-1 w-6 aspect-square flex-shrink-0 text-white   my-auto"/>
            </div>

            <div :class="{'w-full flex-row-reverse': expandedSearchBar,' w-max sm:w-full max-w-md flex-row-reverse ': !expandedSearchBar,
                'rounded-t-md rounded-r-md': expandedSearchResults ,' rounded-md ': !expandedSearchResults}"
                 class="h-10 relative flex sm:gap-x-2 items-center text-zinc-500 p-2 px-3 bg-zinc-900 ">
                <SearchIcon @click="$emit('toggleExpandedSearchBarOn')" class="w-5 h-5 flex-shrink-0"/>
                <input type="text" @click="$emit('toggleExpandedSearchResultsOn')"
                       class="bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white font-bold text-sm"
                       :class="{'w-full': expandedSearchBar,'w-0 sm:w-full': !expandedSearchBar,}"
                       placeholder="Search YouTube, Twitch and more...">

                <!--Search dropdown-->
                <div  :class="{'w-full': expandedSearchBar,' w-max sm:w-full max-w-md': !expandedSearchBar,
                    'flex': expandedSearchResults ,'hidden': !expandedSearchResults}"
                    class="absolute left-0 top-9 w-full pr-11 sm:pl-0  ">

                    <div class="relative w-full bg-zinc-900 dark:bg-zinc-900 border border-zinc-900 h-96
                               py-2 px-3  rounded-b-xl text-white shadow shadow-md">
                        <div class="relative w-full fixed pointer-events absolute rounded-none inset-x-0 mx-auto z-20 ">
                            <div class=" w-full text-sm text-left flex flex-col space-y-1">
                                <Link href="/" class="search-suggestion select-none" >
                                    <div class=" overflow-x-hidden bg-zinc-800 hover:bg-zinc-700 rounded-md ease-in-out duration-400 transition shadow-md shadow">
                                        <div scope="row" class="h-8 overflow-y-hidden flex px-3 py-2 text-base font-medium text text-white ">
                                            <div class="flex-shrink-0 w-4 mr-3 my-auto flex flex-col justify-center items-center">
                                                <SearchIcon class="w-4 h-4"/>
                                            </div>

                                            <div class="line-clamp-1  overflow-y-hidden  flex flex-col justify-center items-center ">
                                                <p class="font-semibold text-left leading-4 my-auto  line-clamp-1 break-words ">
                                                    Pewdiepie
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>


</template>
