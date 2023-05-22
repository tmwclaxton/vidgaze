<template>

    <Head title="Terms" />

    <ConsistentPadding>
        <Terms class="prose mx-auto "/>
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

