
<script setup>
const name = 'PodcastCard';

</script>

<template>
    <div class="cursor-pointer select-none rounded   w-full  ">
        <div class=" relative  group">
            <a href="">
                <img class="w-full md:w-72 block rounded "
                     src="https://e-cdns-images.dzcdn.net/images/talk/7ddf846d7cae9fdd74cdca2a4dfb74d6/264x264-000000-80-0-0.jpg" alt=""/>
            </a>
            <div class="absolute bg-black rounded pointer-events-none
        bg-opacity-0 group-hover:bg-opacity-40 group-hover:opacity-100
        w-full h-full top-0 flex items-center transition justify-evenly duration-300">

                <div class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-3 ">
                    <div class="rounded-full bg-white w-10 h-10 flex">
                        <font-awesome-icon :icon="['fas', 'play']"  class="pl-1 w-5 h-4 my-auto mx-auto text-black"/>
                    </div>
                </div>
                <div class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-14 ml-1 opacity-0 group-hover:opacity-100 transition duration-300">
                    <div class="rounded-full bg-white w-10 h-10 flex">
                        <font-awesome-icon :icon="['fas', 'heart']" class="  w-5 h-4 my-auto mx-auto text-black"/>
                    </div>
                </div>
            </div>
        </div>
        <a href="">
            <div class="p-2 px-2">
                <h3 class="text dark:textDark font-bold text-md  ">No Such Thing As A Fish</h3>
            </div>
        </a>
    </div>

</template>
