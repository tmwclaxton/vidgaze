// import player.js
import Player from './player.js';
import {toRaw} from "vue";

// make a constructor function for the youtube player extending the player class
class VimeoPlayer extends Player {
    create() {

        let html_collection;

        this.player = new Vimeo.Player(this.playerDivHolderID, {
            id: this.external_id,
            responsive: true,
            autopause: ! this.autoplay
        });

        this.player.on('loaded', () => {
            // wait for player to load then set start time
            this.player.ready().then(function () {
                // find the player by external_id and change ready to true
                this.ready = true;

                // set up
                if (this.autoplay) {
                    this.player.play();
                }
                this.player.setCurrentTime(this.start_time);

                //styling

                // this.debugMessage(document.getElementById(playerDiv.id).firstElementChild);
                document.getElementById(this.playerDivHolderID).firstElementChild.classList.add("h-full", "w-full","p-0", "relative");
                document.getElementById(this.playerDivHolderID).firstElementChild.removeAttribute("style");

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

        this.createPlayer()
    }

    async destroy() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).unload();
        this.destroyPlayer();
        return true;
    }

    async play(i = 0) {
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
        await toRaw(this.player).play()
        this.pausePlayer();
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        this.currentTime = await toRaw(player.player).getCurrentTime();
        return this.currentTime;
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        this.playing = await toRaw(player.player).getPaused();
        return this.playing;
    }



}
