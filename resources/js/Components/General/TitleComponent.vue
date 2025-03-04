<template>
    <div class="flex flex-row gap-2">
        <slot  class="w-6 h-6 my-auto"></slot>
        <p class="font-bold text-2xl ">{{ text }}</p>
    </div>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            required: true
        }
    }
};
</script>
