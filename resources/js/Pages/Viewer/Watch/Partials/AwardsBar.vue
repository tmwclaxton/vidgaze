<template>
    <div  v-if="objectAwards && objectAwards[0] != null">
        <!-- Conditional rendering based on the presence and count of objectAwards -->
        <div
             class="w-max rounded p-1 flex flex-row align-middle mr-5 bg-zinc-200 dark:bg-zinc-900 leading-tight gap-x-1 flex-wrap ">

            <!-- Loop through objectAwards to display each item -->
            <div v-for="(objectAward, key) in objectAwards" :key="key"
                 class="group relative my-auto align-middle inline-flex hover:cursor-pointer flex-row mx-1">
                <img :src="objectAward.award.icon_url"
                     class="border-none my-auto h-6 mr-0.5"  alt=""/>
                <span class="text-sm text dark:textDark font-bold ml-1 my-auto">
          {{ objectAward.total }}
        </span>
            </div>

        </div>
        <!-- Refresh button that triggers the refresh method -->
        <button @click="refreshAwards" type="hidden" />
    </div>
</template>

<script>
export default {
    props: {
        objectAwards: {
            type: Array,
            default: () => []
        },
        type: String,
    },
    methods: {
        refreshAwards() {
            setTimeout(() => {
                // Assuming `this.$emit('refresh')` is used to trigger a parent component's method
                this.$emit('refresh');
            }, 500);
        }
    }
}

</script>
<script setup>
const name = 'AwardsBar';

</script>
<style scoped>
/* Add component-specific styles here if needed */
</style>
