<template>

    <SeoHead
        title="Terms of service"
        description="Read the VidGaze terms of service for using our video and streaming platform."
    />

    <ConsistentPadding>
        <Terms class="prose prose-sky dark:prose-invert mx-auto "/>
    </ConsistentPadding>
</template>


<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";

const name = "Terms";
</script>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Terms from "~/markdown/terms.md";
export default {
    layout: AuthenticatedLayout,
    components: {
        Terms
    }
};
</script>

