// import player.js
import Player from './player.js';
import {toRaw} from "vue";
import {usePlayerStore} from "@/Stores/PlayerStore";

// make a constructor function for the youtube player extending the player class
export default class DailymotionPlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then( () => {
            dailymotion.createPlayer(this.playerDiv, {
                video: this.external_id,
                params: {
                    // startTime: this.startTime, // this doesn't work for some reason
                    autoplay: this.autoplay,
                    mute: false,

                }
            }).then((resolvedPlayer) => {
                // this.playerDiv.removeAttribute('style');

                this.player = resolvedPlayer;

                // don't remove these 2 lines, they are need otherwise the player disappears into the ether
                document.getElementById(this.playerDiv).classList.add("h-full", "w-full", "p-0", "relative");
                document.getElementById(this.playerDiv).removeAttribute('style');

                this.player.on(dailymotion.events.PLAYER_VIDEOCHANGE, () => {
                    this.ready = true;
                    this.player.seek(this.start_time);
                    if (this.autoplay) {
                        this.player.play();
                    }
                });

                this.player.on(dailymotion.events.VIDEO_PLAY, () => {
                    this.ready = true;
                    this.startViewRecord();
                });

                this.player.on(dailymotion.events.VIDEO_PAUSE, () => {
                    this.pauseViewRecord();
                });

                this.player.on(dailymotion.events.VIDEO_END, () => {
                    this.endVideo();
                });

                // this.player.on(dailymotion.events.PLAYER_READY, () => {
                //     this.ready = true;
                // });
            });

            this.createPlayer()

        });
    }

    async remove() {
        if (this.ready === false) {
            return false;
        }
        await this.player.destroy();
        this.destroyPlayer();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            return false;
        }
        await this.player.play();
        this.playPlayer();
        return true;
    }

    async togglePause() {
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
        const state = await this.player.getState();
        this.currentTime = state.videoTime;
        return this.currentTime
    }

    async isPlaying() {
        // console.log("isPlaying called");
        if (this.ready === false) {
            return false;
        }
        const state = await this.player.getState();
        // console.log(state);
        this.playing = state.playerIsPlaying;
        return this.playing;
    }

}
