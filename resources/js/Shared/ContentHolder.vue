<template>
    <div :class="[sidebar ?  'flex sm:grid sm:grid-cols-10' : 'sm:flex']">

        <div class="flex-shrink-0" :class="[sidebar ? 'sm:col-span-3 lg:col-span-2' : 'sm:w-16']"
             :style="!sidebar && { width: '64px' }">
            <!--sidebar can't go in here because sometimes a page might want to use the whole page,
                instead we mimic the sidebar by using the same width and padding as the sidebar
               -->
        </div>
        <!--correctly position content the right of the sidebar -->
        <section class="mx-auto w-full"
                 :class="[sidebar ? 'col-span-8 sm:col-span-7 lg:col-span-8' : 'w-full']">
            <!--content goes here, and gets centered if screen is too large -->
            <div class="relative mb-auto mx-auto max-w-screen-4xl">
                <slot></slot>
            </div>
        </section>
    </div>
</template>
<script>
export default {
    name: "ContentHolder",
    props: {
        sidebar: {
            type: Boolean,
            default: false
        },
    }
}
</script>

