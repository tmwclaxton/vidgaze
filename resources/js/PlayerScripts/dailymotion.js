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
                    usePlayerStore().startViewRecord(this.external_id);
                });

                this.player.on(dailymotion.events.VIDEO_PAUSE, () => {
                    usePlayerStore().pauseViewRecord(this.external_id);
                });

                this.player.on(dailymotion.events.VIDEO_END, () => {
                    usePlayerStore().endVideo(this.external_id);
                });

                // this.player.on(dailymotion.events.PLAYER_READY, () => {
                //     this.ready = true;
                // });
            });

            this.createPlayer()

        });
    }

    async remove() {
        if (this.ready === false || this.isLocked()) {
            return false;
        }
        await this.player.destroy();
        this.destroyPlayer();
        return true;
    }

    async togglePlay() {
        if (this.ready === false || this.isLocked()) {
            return false;
        }
        await this.player.play();
        this.playPlayer();
        return true;
    }

    async togglePause() {
        if (this.ready === false || this.isLocked()) {
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
