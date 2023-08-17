// import player.js
import Player from './player.js';
import {toRaw} from "vue";

// make a constructor function for the youtube player extending the player class
class DailymotionPlayer extends Player {
    create() {
        dailymotion.createPlayer(this.playerDivHolderID, {
            video: this.external_id,
            params: {
                startTime: this.startTime,
                autoplay: this.autoplay,
                mute: false,

            }
        }).then((resolvedPlayer) => {
            this.player = resolvedPlayer;

            // don't remove these 2 lines, they are need otherwise the player disappears into the ether
            document.getElementById(this.playerDivHolderID).classList.add("h-full", "w-full", "p-0", "relative");
            document.getElementById(this.playerDivHolderID).removeAttribute('style');

            this.player.on(dailymotion.events.PLAYER_VIDEOCHANGE, () => {
                // this.debugMessage('BUILDDailymotion: Dailymotion player ready')
                if (this.autoplay) {
                    this.player.play();
                }
            });

            this.player.on(dailymotion.events.VIDEO_PLAY, () => {
                this.startViewRecord();
            });

            this.player.on(dailymotion.events.VIDEO_PAUSE, () => {
                this.pauseViewRecord();
            });

            this.player.on(dailymotion.events.VIDEO_END, () => {
                this.endVideo();
            });

            this.player.on(dailymotion.events.PLAYER_READY, () => {
                this.ready = true;
            });
        });

        this.createPlayer()
    }

    async destroy() {
        if (this.ready === false) {
            return false;
        }
        await this.player.destroy();
        this.destroyPlayer();
        return true;
    }

    async play(i = 0) {
        if (this.ready === false) {
            return false;
        }
        await this.player.play();
        this.playPlayer();
        return true;
    }

    async pause() {
        if (this.ready === false) {
            return false;
        }
        await this.player.pause();
        this.pausePlayer();
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        const state = await player.player.getState();
        this.currentTime = state.videoTime;
        return this.currentTime
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        const state = await player.player.getState();
        this.playing = state.playerIsPlaying;
        return this.playing;
    }

}
