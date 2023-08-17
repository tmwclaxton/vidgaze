// import player.js
import Player from './player.js';
import {toRaw} from "vue";

// make a constructor function for the youtube player extending the player class
class TwitchPlayer extends Player {
    create() {
        this.player = new Twitch.Player(this.playerDivHolderID, {
            channel: this.external_id,
            parent: ["localhost","127.0.0.1","vidgaze.tv","www.vidgaze.tv","www.staging.vidgaze.tv","staging.vidgaze.tv"],
            width: '100%',
            height: '100%',
            autoplay: this.autoplay ? 1 : 0,
            controls: true,
        });

        // on play start view record
        this.player.addEventListener(Twitch.Player.PLAY, () => {
            this.startViewRecord();
        });

        // on pause stop view record
        this.player.addEventListener(Twitch.Player.PAUSE, () => {
            this.pauseViewRecord();
        });

        // on video end stop view record
        this.player.addEventListener(Twitch.Player.ENDED, () => {
            this.endVideo();
        });

        this.player.addEventListener(Twitch.Player.READY, () => {
            this.ready = true;
        });

        this.createPlayer()
    }

    destroy() {
        if (this.ready === false) {
            return false;
        }
        this.player.destroy();
        this.destroyPlayer();
        return true;
    }

    async play() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).play();
        this.playPlayer();
        return true;
    }

    async pause() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).pause();
        this.pausePlayer();
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
