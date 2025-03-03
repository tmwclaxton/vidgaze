
<script setup>
const name = 'CategoryCard';
</script>

<template>
    <div class="w-full relative rounded  overflow-hidden group cursor-pointer ">
        <a href="">
            <img class="w-full block " src="https://e-cdns-images.dzcdn.net/images/misc/48ac252966b54256ecba73cd54e1d100/134x264-000000-80-0-0.jpg">
            <div
                class="absolute bg-black rounded bg-opacity-0 group-hover:bg-opacity-40 w-full h-full
                                         top-0 flex items-center group-hover:opacity-100 transition justify-evenly duration-300">

                <div class="flex flex-row gap-x-2 a ">
                    <p class="w-full text-zinc-100 text text-2xl text-center font-extrabold mx-auto">New Podcast Rewind</p>
                </div>
            </div>
        </a>
    </div>
</template>
