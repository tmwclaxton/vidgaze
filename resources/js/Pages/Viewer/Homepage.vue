<script setup>

</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
    props: {
        showingStudioLinks: {
            type: Boolean,
            required: true
        }
    }
};
</script>

<template>
        <Head title="Home" />

        <div class="-mt-16 "></div>
                <div class="mt-80">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="  overflow-hidden shadow-sm sm:rounded-lg">

                        </div>
                    </div>
                </div>


</template>
