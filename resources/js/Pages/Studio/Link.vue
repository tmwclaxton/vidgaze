<template>

</template>


<script>
export default {
    layout: null,
};
</script>

<script setup>

import {onMounted} from "vue";

onMounted(() => {
    const platform = window.location.pathname.split('/').pop();
    const params = new URLSearchParams(window.location.search);

    axios.get('/api/v1/studio/link/' + platform + '?' + params.toString())
        .then(response => {
            console.log(response.data);
            // window.location.href = route('studio.dashboard');
        })
        .catch((error) => {
            console.log(error);
            console.error("Could not link " + platform + " account");
            // window.location.href = route('studio.dashboard');
        });
});

</script>
