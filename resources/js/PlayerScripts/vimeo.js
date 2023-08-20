// import player.js
import Player from './player.js';
import {toRaw} from "vue";

// make a constructor function for the youtube player extending the player class
export default class VimeoPlayer extends Player {
    loaded = false;
    async create() {
        // this.playerDiv.removeAttribute('style');
        await this.getStartTimePlayer().then(async () => {

            this.player = new Vimeo.Player(this.playerDiv.id, {
                id: 855016876, // this.external_id,
                responsive: true,
                autopause: !this.autoplay
            });
            console.log(await toRaw(this.player));

            if (this.loaded === false) {
                await toRaw(this.player).on('loaded', async () => {
                    // wait for player to load then set start time
                    await toRaw(this.player).ready().then(function () {
                        this.loaded = true;
                        this.playerSetup();
                    }.bind(this));
                });
            } else {
                this.playerSetup();
            }

            await toRaw(this.player).on('play', () => {
                this.startViewRecord();
            });

            await toRaw(this.player).on('pause', () => {
                this.pauseViewRecord();
            });

            await toRaw(this.player).on('ended', () => {
                this.endVideo();
            });

            this.createPlayer();
        });
    }

    async playerSetup() {
        let html_collection;

        console.log("vimeo player ready");
        // find the player by external_id and change ready to true
        this.ready = true;

        // set up
        if (this.autoplay) {
            await this.togglePlay();
        }
        await toRaw(this.player).setCurrentTime(this.start_time);

        //styling
        document.getElementById(this.playerDiv.id).firstElementChild.classList.add("h-full", "w-full", "p-0", "relative");
        document.getElementById(this.playerDiv.id).firstElementChild.removeAttribute("style");

        // reset all Vimeo players to default size
        html_collection = document.getElementsByClassName("player");
        for (let i = 0; i < html_collection.length; i++) {
            html_collection[i].removeAttribute("style");
        }
    }

    async remove() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).destroy();
        this.destroyPlayer();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            console.log("vm not ready");
            return false;
        }
        await toRaw(this.player).play();
        this.playPlayer();
        return true;

    }

    async togglePause() {
        if (this.ready === false) {
            console.log("vm not ready");
            return false;
        }
        await toRaw(this.player).pause()
        this.pausePlayer();
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            console.log("vm not ready");
            return false;
        }
        this.currentTime = await toRaw(this.player).getCurrentTime();
        return this.currentTime;
    }

    async isPlaying() {
        if (this.ready === false) {
            console.log("vm not ready");
            return false;
        }
        this.playing = !await toRaw(this.player).getPaused();
        return this.playing;
    }



}
