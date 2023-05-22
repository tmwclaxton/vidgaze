
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
        playerStore.buildPlayer();
    };
});






</script>
