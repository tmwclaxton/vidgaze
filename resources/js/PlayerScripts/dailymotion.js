// import player.js
import Player from './player.js';
import {toRaw} from "vue";
import {usePlayerStore} from "@/Stores/PlayerStore";

export default class DailymotionPlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then( () => {
            dailymotion.createPlayer(this.playerDiv.id, {
                video: this.external_id,
                params: {
                    // startTime: this.start_time, // this doesn't work for some reason
                    autoplay: this.autoplay ? 1 : 0,
                    mute: false,

                }
            }).then((resolvedPlayer) => {
                this.player = resolvedPlayer;

                this.player.on(dailymotion.events.PLAYER_VIDEOCHANGE, () => {
                    this.ready = true;

                    // don't remove these 2 lines, they are need otherwise the player disappears into the ether
                    document.getElementById(this.playerDiv.id).classList.add("h-full", "w-full", "p-0", "relative");
                    document.getElementById(this.playerDiv.id).removeAttribute('style');
                    if (this.autoplay) {
                        this.togglePlay();
                    } else {
                        this.togglePause();
                    }
                });

                this.player.on(dailymotion.events.VIDEO_PLAY, async () => {
                    if (!this.seeked) {
                        this.seeked = true;
                        setTimeout(async () => {
                            console.log("seeking to " + this.start_time)
                            await this.player.seek(this.start_time);
                        }, 1000);
                    }

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

    async removePlayer() {
        if (this.ready === false) {
            // console.log("dm player not ready");
            return false;
        }
        await this.player.destroy();
        this.resetPlayerValues();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            // console.log("dm player not ready");
            return false;
        }
        await this.player.play();
        this.playing = true;
        return true;
    }

    async togglePause() {
        if (this.ready === false) {
            // console.log("dm player not ready");
            return false;
        }
        await this.player.pause();
        this.playing = false;
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            // console.log("dm player not ready");
            return false;
        }
        const state = await this.player.getState();
        this.currentTime = state.videoTime;
        return this.currentTime
    }

    async isPlaying() {
        // console.log("isPlaying called");
        if (this.ready === false) {
            // console.log("dm player not ready");
            return false;
        }
        const state = await this.player.getState();
        // console.log(state);
        this.playing = state.playerIsPlaying;
        return this.playing;
    }

}
