// import player.js
import Player from './player.js';
import {toRaw} from "vue";
import {usePlayerStore} from "@/Stores/PlayerStore";

// make a constructor function for the youtube player extending the player class
export default class VimeoPlayer extends Player {
    async create() {
        // this.playerDiv.removeAttribute('style');
        await this.getStartTimePlayer().then(() => {
            let html_collection;

            this.player = new Vimeo.Player(this.playerDiv.id, {
                id: 855016876, // this.external_id,
                responsive: true,
                autopause: !this.autoplay
            });

            this.player.on('loaded', () => {
                // wait for player to load then set start time
                this.player.ready().then(function () {

                    console.log("vimeo player ready, start time: " + this.start_time + " autoplay: " + this.autoplay);
                    // find the player by external_id and change ready to true
                    this.ready = true;

                    // set up
                    if (this.autoplay) {
                        this.togglePlay();
                    }
                    this.player.setCurrentTime(this.start_time);

                    //styling

                    // this.debugMessage(document.getElementById(playerDiv.id).firstElementChild);
                    document.getElementById(this.playerDiv.id).firstElementChild.classList.add("h-full", "w-full", "p-0", "relative");
                    document.getElementById(this.playerDiv.id).firstElementChild.removeAttribute("style");

                    // reset all Vimeo players to default size
                    html_collection = document.getElementsByClassName("player");
                    for (let i = 0; i < html_collection.length; i++) {
                        html_collection[i].removeAttribute("style");
                    }
                }.bind(this));
            });

            this.player.on('play', () => {
                this.startViewRecord();
            });

            this.player.on('pause', () => {
                this.pauseViewRecord();
            });

            this.player.on('ended', () => {
                this.endVideo();
            });

            this.createPlayer();
        });
    }

    async remove() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).unload();
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
