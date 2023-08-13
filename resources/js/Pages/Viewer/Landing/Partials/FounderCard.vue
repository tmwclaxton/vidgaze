<template>
    <div class=" w-full overflow-hidden text-center border dark:border-none rounded-xl  dark:bg-vidgaze-blue-nav hover:-translate-y-2 ease-in-out duration-300 transition  cursor-pointer  ">
        <img class=" w-full max-h-64 aspect-square object-contain" v-bind:src="img" />
        <div class="p-4">
            <div class="text-md">
                <a href="#" class="font-bold text dark:textDark " v-text="fullname"></a>
                <p class="text-sm uppercase text dark:textDark" v-text="description"></p>
            </div>
            <div class="flex items-center justify-center my-4 gap-x-2">

                <a v-bind:href="instagram">
                    <!--<x-icon name="instagram" />-->
                    <font-awesome-icon :icon="['fab', 'instagram']" class="w-5 rounded aspect-square"/>
                </a>
                <a v-bind:href="github"  >
                    <font-awesome-icon :icon="['fab', 'github']" class="w-5  aspect-square"/>
                </a>
                <a v-bind:href="linkedin">
                    <font-awesome-icon :icon="['fab', 'linkedin']" class="w-5 rounded aspect-square  "/>
                </a>


            </div>
        </div>
    </div>

</template>
<script setup>

const props = defineProps({
    img: {
        type: String,
        required: true
    },
    fullname: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    instagram: {
        type: String,
        required: true
    },
    github: {
        type: String,
        required: true
    },
    linkedin: {
        type: String,
        required: true
    },
})

</script>

