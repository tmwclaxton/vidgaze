<script setup>
const props = defineProps({
    visibility: {
        type: String,
        required: true
    },
});
</script>
<template>
    <font-awesome-icon :icon="['fas', 'lock']" v-if="visibility === 'private'"/>
    <font-awesome-icon :icon="['fas', 'earth-americas']" v-if="visibility === 'public'"/>
    <font-awesome-icon :icon="['fas', 'link']" v-if="visibility === 'unlisted'"/>
</template>

