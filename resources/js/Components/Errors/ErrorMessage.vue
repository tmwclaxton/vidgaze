<script setup>
    const name = 'Error';
    const props = defineProps({
        message: {
            type: String,
            required: true,
        },
    });
</script>
<template>
    <div class="flex flex-col items-center align-middle flex-grow my-5">
        <div class="my-auto">
            <img src="/images/mascot/Confused.png" alt="Confused Mascot"
                 class="  max-h-64 mx-auto object-contain"/>
            <p class="normal-case text dark:textDark  text-center text-base mt-5 font-semibold" v-text="props.message"></p>
        </div>
    </div>
</template>
