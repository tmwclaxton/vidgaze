
<script setup>
const name = 'PodcastCreatorCard';

</script>

<template>
    <div class="cursor-pointer select-none  w-full  ">
        <div class=" relative  group rounded-full overflow-hidden">
            <img class="w-full aspect-square block    "
                 src="https://render.fineartamerica.com/images/rendered/default/poster/8/8/break/images/artworkimages/medium/1/jre-joe-rogan-hadi-maxel.jpg" alt=""/>
            <div class="absolute bg-black rounded bg-opacity-0 group-hover:bg-opacity-40 w-full h-full
                                     top-0 flex items-center group-hover:opacity-100 transition justify-evenly duration-300">

                <div class="  absolute my-auto mx-auto opacity-0 group-hover:opacity-100
                                 transform translate-y-1 group-hover:translate-y-0 duration-300 transition">
                    <div class="rounded-full bg-white w-10 h-10 flex">
                        <font-awesome-icon :icon="['fas', 'headphones']" class="  w-5 h-4 my-auto mx-auto text-black "/>
                    </div>
                </div>
            </div>
        </div>
        <div class="py-5 w-full">
            <h3 class="text dark:textDark font-bold text-lg text-center ">Joe Rogan</h3>
        </div>
    </div>

</template>
