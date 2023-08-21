// import player.js
import Player from './player.js';
import {toRaw} from "vue";
import {usePlayerStore} from "@/Stores/PlayerStore";

export default class TwitchPlayer extends Player {
    async create() {
        this.player = new Twitch.Player(this.playerDiv, {
            channel: "valorant", //this.external_id,
            parent: ["localhost","127.0.0.1","vidgaze.tv","www.vidgaze.tv","www.staging.vidgaze.tv","staging.vidgaze.tv"],
            width: '100%',
            height: '100%',
            autoplay: this.autoplay ? 1 : 0,
            controls: true,
        });

        // on play start view record
        // this.player.addEventListener(Twitch.Player.PLAY, () => {
        //     usePlayerStore().startViewRecord(this.external_id);
        // });

        // on pause stop view record
        // this.player.addEventListener(Twitch.Player.PAUSE, () => {
        //     usePlayerStore().pauseViewRecord(this.external_id);
        // });

        // on video end stop view record
        this.player.addEventListener(Twitch.Player.ENDED, () => {
            usePlayerStore().endVideo(this.external_id);
        });

        this.player.addEventListener(Twitch.Player.READY, () => {
            this.ready = true;
        });

        this.createPlayer()
    }

    async removePlayer() {
        if (this.ready === false) {
            return false;
        }
        this.player.destroy();
        this.resetPlayerValues();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).play();
        this.playing = true;
        return true;
    }

    async togglePause() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).pause();
        this.playing = false;
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        this.currentTime = await toRaw(this.player).getCurrentTime();
        return this.currentTime
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        this.playing = await toRaw(this.player).isPaused();
        return this.playing;
    }
}
