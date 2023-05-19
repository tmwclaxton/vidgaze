<template>
    <div class="w-full h-full flex flex-col gap-3 ">
        <div class="bg-zinc-200 dark:bg-zinc-800 animate-pulse rounded-xl h-64 w-full "></div>
        <div class="flex flex-row gap-2 ">
            <div class="bg-zinc-200 dark:bg-zinc-800 animate-pulse rounded-md flex-grow h-7 my-auto w-5/12 "></div>
        </div>
        <div class="flex flex-row gap-2 ">
            <div class="bg-zinc-200 dark:bg-zinc-800 animate-pulse rounded-xl flex-grow h-6 my-auto w-2/6 "></div>
            <div class="bg-zinc-200 dark:bg-zinc-800 animate-pulse rounded-xl flex-grow h-6 my-auto w-2/6 "></div>
            <div class="  animate-pulse rounded-md flex-grow h-7 my-auto w-1/6  "></div>
        </div>
    </div>
</template>

<script setup>
const name = 'CategorySkeleton';
</script>
