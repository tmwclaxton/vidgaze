<template>

    <Head title="Policy" />

    <ConsistentPadding>
        <Policy class="prose mx-auto "/>
    </ConsistentPadding>
</template>


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

<style scoped>

</style>
