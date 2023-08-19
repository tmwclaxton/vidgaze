// import player.js
import Player from './player.js';
import {usePlayerStore} from "@/Stores/PlayerStore";

// make a constructor function for the youtube player extending the player class
export default class YouTubePlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then(() => {
            // check if window.YT exists
            if (window.YT === undefined) {
                console.log('window.YT is undefined, trying again in 1 second ' + this.external_id);
                setTimeout(() => {
                    this.create();
                }, 2000)
                return;
            }

            this.player = new window.YT.Player(this.playerDiv, {
                videoId: this.external_id,
                playerVars: {
                    'autoplay': this.autoplay ? 1 : 0,
                    'controls': 1,
                    'modestbranding': 1,
                    'rel': 0,
                    'showinfo': 0,
                    'start': this.startTime,
                },
                events: {
                    // when YouTube video ends run the endVideo function
                    onStateChange: (event) => {
                        if (event.data === 0) { // this state means the video has ended
                            // this.debugMessage('BUILDYouTube: YouTube video ended')
                            usePlayerStore().endVideo(this.external_id);
                        }
                        if (event.data === 1) { // this state means the video is playing
                            // this.debugMessage('BUILDYouTube: YouTube video playing')
                            usePlayerStore().startViewRecord(this.external_id);
                        }
                        if (event.data === 2) { // this state means the video is paused
                            // this.debugMessage('BUILDYouTube: YouTube video paused')
                            usePlayerStore().pauseViewRecord(this.external_id);
                        }
                        if (event.data === 3) { // this state means the video is buffering
                            // this.debugMessage('BUILDYouTube: YouTube video buffering')
                            usePlayerStore().pauseViewRecord(this.external_id);
                        }
                    },
                    onReady: (event) => {
                        // find the player by external_id and change ready to true
                        // console.log('BUILDYouTube: YouTube player ready')
                        this.ready = true;
                    }
                }
            });

            this.createPlayer()
        });
    }

    async remove() {
        if (this.ready === false || this.isLocked()) {
            return false;
        }
        this.player.destroy();
        this.destroyPlayer();
        return true;
    }

    async togglePlay() {
        if (this.ready === false || this.isLocked()) {
            return false;
        }
        this.player.playVideo();
        this.playPlayer();
        return true;
    }

    async togglePause() {
        if (this.ready === false || this.isLocked()) {
            return false;
        }
        this.player.pauseVideo();
        this.pausePlayer();
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        this.currentTime = await this.player.getCurrentTime();
        return this.currentTime;
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        this.playing = await this.player.getPlayerState() === 1;
        return this.playing;
    }
}
