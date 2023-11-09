<script setup>
const name = "ExternalCommentSection"
const props = defineProps({
    'external_id': {
        type: String,
        required: true
    },
    'source': {
        type: String,
        required: true
    },
})
</script>

<template>
    <div class="w-full h-full">
        <iframe v-if="props.source === 'YouTube'" class="w-full h-full without-ring"
                :src="'https://www.youtube.com/live_chat?v=' + props.source + '&embed_domain=127.0.0.1&embed_domain=vidgaze.tv&embed_domain=www.vidgaze.tv&embed_domain=www.staging.vidgaze.tv&embed_domain=staging.vidgaze.tv'"></iframe>

        <iframe v-else-if="props.source === 'Twitch'"
            id="chat_embed"
            :src="'https://www.twitch.tv/embed/' + props.source + '/chat?parent=localhost&127.0.0.1&parent=vidgaze.tv&parent=www.vidgaze.tv&parent=www.staging.vidgaze.tv&parent=staging.vidgaze.tv'"
            class="w-full h-full without-ring">
        </iframe>
    </div>

</template>
