
<script setup>
const name = 'ShortsPlayerSkeleton';

</script>

<template>
    <section class="w-full h-[calc(100vh-4rem)]   snap-start flex flex-col" >

        <div class="relative flex pt-8 py-10  h-full flex flex-col">

            <div class=" mx-auto flex-grow flex flex-row align-middle justify-center">

                <div class=" aspect-[9/16] h-full flex-grow flex flex-col">

                    <div class="w-full h-full rounded-xl without-ring flex flex-col relative overflow-hidden">

                        <!--Channel information-->
                        <div class="pb-3 gap-x-2 flex flex-row ">
                            <div class="flex-shrink-0   cursor-pointer w-14 h-14 rounded-full bg-zinc-200 dark:bg-zinc-800 animate-pulse ">
                            </div>
                            <div class="flex-shrink-0   cursor-pointer w-72 h-14 rounded-full bg-zinc-200 dark:bg-zinc-800 animate-pulse ">
                            </div>
                        </div>

                        <!--Player-->
                        <div class="bg-zinc-200 dark:bg-zinc-800 animate-pulse w-full h-full rounded-2xl without-ring flex relative overflow-hidden">


                        </div>
                    </div>

                </div>
                <!--Buttons-->
                <div class="flex flex-col gap-4 select-none justify-end text dark:textDark">
                    <div v-for="n in 4" class="flex flex-col gap-1 p-2 ml-6 cursor-pointer w-14 aspect-square rounded-full bg-zinc-200 dark:bg-zinc-800 animate-pulse ">
                    </div>
                </div>

            </div>

        </div>

    </section>

</template>
