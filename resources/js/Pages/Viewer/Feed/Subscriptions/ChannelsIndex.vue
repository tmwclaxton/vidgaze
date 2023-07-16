<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";

const name = 'List of subscriptions page';


</script>



<template>
    <Head>
        <title>Channels</title>
    </Head>


    <ConsistentPadding class="px-12">



    </ConsistentPadding>




</template>
