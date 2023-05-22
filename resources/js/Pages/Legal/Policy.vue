

<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";

const name = "Policy";
</script>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Policy from "~/markdown/policy.md";
export default {
    layout: AuthenticatedLayout,
    components: {
        Policy
    }
};
</script>

<template>

    <Head title="Policy" />

    <ConsistentPadding>
        <Policy class="prose prose-sky mx-auto "/>
    </ConsistentPadding>
</template>

