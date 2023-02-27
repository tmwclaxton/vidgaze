<template>

    <nav id="topNavigation" class="transition duration w-full fixed z-40 top-0 bg-vidgaze-blue px-3  w-full h-16 flex flex-row align-middle justify-items-center">

            <div class="flex flex-row h-10  my-auto w-full">
                <div class="   text-white flex flex-shrink items-center   ">

                    <!--this is the side menu button-->
                    <button
                            type="button"
                            class="  without-ring hidden xs:inline-flex items-center p-2 text-sm top-nav-button rounded-lg">
                        <!--<x-icon name="3lines" class="w-6 h-6"/>-->
                    </button>
                    <Link href="/" preserve-scroll class=" xs:ml-0 flex items-center without-ring flex-shrink-0">
                        <img src="/images/logo/vidgaze_banner.png" class="h-12 without-ring" alt="Logo">
                    </Link>
                </div>

                <!--this is where the search bar goes-->
                <div class="relative flex 2xl:mx-auto h-full flex-grow mx-5">

                    <SearchBar></SearchBar>

                </div>

                <!--this div holds the buy VidCoins button, search, create, notification login and profile buttons-->
                <div class="  justify-end align-middle justify-items-center items-center flex-row flex space-x-5 float-right flex-grow sm:flex-grow-0 ">

                    <!--Buy VidCoins Button-->
                    <div class="hidden lg:flex select-none">
                        <!--<x-nav-button  link="{{ route('marketplace') }}" >-->
                            <Link href="/">
                                <div class="flex flex-row gap-x-2 ">
                                    <img alt="A pile of VidCoins" src="/images/vidcoins/coins/PileofCoins2.png"
                                         class="h-5 object-contain group-hover:shake "/>
                                    <p class="text-sm text-center font-bold whitespace-nowrap">Buy VidCoins</p>
                                </div>
                            </Link>
                    </div>

                    <!--Search Button, TODO: onclick minSearchBar = ! minSearchBar"-->
                    <div class="
                        top-nav-button opacity-100 focus:opacity-80 flex sm:hidden " >
                        <svg class="w-6 pl-1" fill="currentColor" viewBox="0 0 461.516 461.516"
                             xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <path
                                    d="m185.746 371.332c41.251.001 81.322-13.762 113.866-39.11l122.778 122.778c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077l-122.778-122.778c62.899-80.968 48.252-197.595-32.716-260.494s-197.594-48.252-260.493 32.716-48.252 197.595 32.716 260.494c32.597 25.323 72.704 39.06 113.982 39.039zm-98.651-284.273c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002c-.001-.001-.001-.001-.002-.002-54.484-54.087-54.805-142.101-.718-196.585.239-.24.478-.479.718-.718z"/>
                            </g>
                        </svg>
                    </div>


                    <!--TODO: when logged in display stuff below-->

                    <!--Create button TODO: when clicked createDropdown = ! createDropdown-->
                    <button class="top-nav-button  hidden sm:block without-ring">
                        <!--TODO: sort out icons-->
                        <!--<x-icon name="cloud_upload" class="w-6"/>-->
                    </button>


                    <!--Notification button TODO: when clicked notificationDropdown = ! notificationDropdown
                    and trigger something to load notifcations-->
                    <button class="top-nav-button mr-2 dropdown-toggle without-ring hidden-arrow flex items-center"
                             id="dropdownMenuButton1" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <!--<x-icon name="bell" class="w-5"/>-->
                        <!--Notification counter design-->
                        <!--<span class="text-white dark:text-zinc-200 bg-red-700 absolute rounded-full text-xs font-bold-->
                        <!-- -mt-2.5 ml-2 py-0 px-1.5">-->
                        <!--1-->
                        <!--</span>-->
                    </button>


                    <!--Profile button TODO: when clicked profileDropdown = ! profileDropdown-->
                    <button type="button"
                            class=" h-9  aspect-square text-sm bg-zinc-800 rounded-full md:mr-0 " id="user-menu-button"
                            aria-expanded="false"
                            data-dropdown-toggle="dropdown">

                        <!--if user has an avatar_url-->
                            <!--<img class="h-full aspect-square rounded-full  "-->
                            <!--     src="{{Auth::user()->creator->avatar_url}}">-->
                        <!--if user does not have an avatar_url-->
                            <!--<x-icon name="profile_default" style="padding:2px;"-->
                            <!--        class="  bg-vidgaze-blue text dark:textDark  w-full h-full  "/>-->

                    </button>

                </div>

                <!--When logged out show stuff below-->

                <!--Sign in button-->
                <div class="pr-2">
                    <!--TODO:refactor nav button-->
                    <!--<x-nav-button  text="Sign in" link="{{route('login')}}">-->
                    <!--    <x-icon name="profile" class="w-5  "/>-->
                    <!--</x-nav-button>-->
                </div>

            </div>

    </nav>
<br><br>

    <nav class="p-5 w-full bg-vidgaze-blue text dark:textDark flex flex-row gap-x-5">
        <Link href="/" :class="{'font-bold underline': $page.url === '/'}">Home</Link>
        <Link href="/users"  :class="{'font-bold underline': $page.url === '/users'}">Users</Link>
        <Link href="/settings"  :class="{'font-bold underline': $page.url === '/settings'}">Settings</Link>
        <Link href="/search"  :class="{'font-bold underline': $page.url === '/search'}">Search</Link>
        <Link href="/logout1" method="post" as="button" >Logout</Link>
    </nav>


    <!--<iframe class="fixed bottom-0 right-5 w-72 h-52 z-50" style="border-radius:12px" src="https://open.spotify.com/embed/playlist/37i9dQZF1DX4sWSpwq3LiO?utm_source=generator" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>-->

</template>

<script>
import {ref} from "vue";
import SearchBar from "./SearchDropdown/SearchBar";

export default {
    name: "TopNavigation",
    components: {SearchBar},
    setup() {
        const expandedSearchBar = ref(false);
        const minimiseSearchBar = (event) => {
            expandedSearchBar.value = false;
        }

        return { expandedSearchBar, minimiseSearchBar }
    }
}
</script>

