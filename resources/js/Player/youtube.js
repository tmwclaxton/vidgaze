// import player.js
import Player from './player.js';

// make a constructor function for the youtube player extending the player class
class YouTubePlayer extends Player {
    create() {
        // check if window.YT exists
        if (window.YT === undefined) {
            console.log('window.YT is undefined, trying again in 1 second ' + this.external_id);
            setTimeout(() => {
                this.create();
            } ,  2000)
            return;
        }

        this.player = new window.YT.Player(this.playerDivHolderID, {
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
                        this.endVideo();
                    }
                    if (event.data === 1) { // this state means the video is playing
                        // this.debugMessage('BUILDYouTube: YouTube video playing')
                        this.startViewRecord();
                    }
                    if (event.data === 2) { // this state means the video is paused
                        // this.debugMessage('BUILDYouTube: YouTube video paused')
                        this.pauseViewRecord();
                    }
                    if (event.data === 3) { // this state means the video is buffering
                        // this.debugMessage('BUILDYouTube: YouTube video buffering')
                        this.pauseViewRecord();
                    }
                },
                onReady: (event) => {
                    // find the player by external_id and change ready to true
                    console.log('BUILDYouTube: YouTube player ready')
                    this.ready = true;
                }
            }
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

    play(i = 0) {
        if (this.ready === false) {
            return false;
        }
        this.player.playVideo();
        this.playPlayer();
        return true;
    }

    pause() {
        if (this.ready === false) {
            return false;
        }
        this.player.pauseVideo();
        this.pausePlayer();
        return true;
    }

    getCurrentTime() {
        if (this.ready === false) {
            return false;
        }
        return this.player.getCurrentTime();
    }



}
