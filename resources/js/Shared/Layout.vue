<template>
    <InertiaHead>

        <title>VidGaze</title>
        <meta name="description" content="VidGaze is a platform that combines YouTube, Dailymotion, Vimeo, Twitch and many more platforms in one app. We also have features to empower creators."
        head-key="description" />
        >

    </InertiaHead>

    <TopNavigation></TopNavigation>
    <slot></slot>
</template>
<p class="text dark:textDark">Welcome back, {{ username }}</p>

<script setup>
import TopNavigation from "./TopNavigation";
import {InertiaHead} from "@inertiajs/inertia-vue3";
</script>
<script>
export default {
    name: "Layout",
    computed: {
        username() {
            return this.$page.props.auth.user.name;
        }
    }

}

</script>

<style scoped>

</style>
