<template>
    <div id="player_div" className="w-full h-full"></div>
</template>

<script setup>
import {onMounted, ref, watch, defineProps} from 'vue';
import {usePlayerStore} from "@/Stores/PlayerModalStore";
const playerStore = usePlayerStore();


let player;

onMounted(() => {
    console.log('mounted');
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';

    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    window.onYouTubeIframeAPIReady = () => {
        buildPlayer();
    };
});

const buildPlayer = () => {
    player = new window.YT.Player('player_div', {
        videoId: playerStore.object.id,
        playerVars: {
            autoplay: playerStore.autoplay ? 1 : 0,
            controls: 1,
            modestbranding: 1,
            rel: 0,
            showinfo: 0,
            start: playerStore.start_time,
        },
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange,
        },
    });
};

const onPlayerReady = (event) => {
    if (props.play) {
        event.target.playVideo();
    }
};

const onPlayerStateChange = (event) => {
    const playerState = event.data;
};


</script>
