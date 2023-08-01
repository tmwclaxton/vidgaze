<template>
    <div class="flex items-center w-full">
        <input
            type="datetime-local"
            :value="dateTimeValue"
            @input="updateDateTimeValue"
            :min="minDateTime"
            class="dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-500 dark:focus:border-blue-600
      border focus:ring-2 border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm"
        />
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed } from 'vue';

const { modelValue } = defineProps(['modelValue']);

const dateTimeValue = ref(toDateTimeLocal(modelValue));

const emit = defineEmits();

const minDateTime = computed(() => {
    const now = new Date();
    return toDateTimeLocal(now.getTime() / 1000); // Convert Unix time to DateTimeLocal format
});

function updateDateTimeValue(event) {
    dateTimeValue.value = event.target.value;
    const unixTime = new Date(event.target.value).getTime() / 1000; // Convert DateTimeLocal to Unix time
    emit('update:modelValue', unixTime);
}

function toDateTimeLocal(unixTime) {
    const date = new Date(unixTime * 1000);
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const day = String(date.getUTCDate()).padStart(2, '0');
    const hours = String(date.getUTCHours()).padStart(2, '0');
    const minutes = String(date.getUTCMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
</script>

<style scoped>
/* Custom styles to change the calendar icon color to white in dark mode with a transition effect */
.dark input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    transition: filter 0.3s ease; /* Add a 0.3s transition to the filter property */
}

/* Set the initial state to black (inverted) for the calendar icon */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    filter: invert(0);
}
</style>
