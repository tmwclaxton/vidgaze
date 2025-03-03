import Player from './player.js';
import { usePlayerStore } from "@/Stores/PlayerStore";

export default class RumblePlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then(() => {
            // Create an iframe element for the Rumble video
            const iframe = document.createElement('iframe');
            iframe.id = `rumble-player-${this.external_id}`;
            iframe.src = `https://rumble.com/embed/${this.external_id}/?autoplay=${this.autoplay ? 1 : 0}`;
            iframe.allow = "autoplay";
            // iframe.frameBorder = "0";
            // iframe.scrolling = "no";
            iframe.style.width = "100%";
            iframe.style.height = "100%";

            // Append the iframe to the playerDiv
            this.playerDiv.appendChild(iframe);
            this.player = iframe;

            // Add event listeners for the iframe
            this.player.onload = () => {
                this.ready = true;
                if (this.autoplay) {
                    this.togglePlay();
                } else {
                    this.togglePause();
                }
            };

            // Listen for messages from the Rumble iframe
            window.addEventListener('message', this.handleMessage.bind(this));

            this.createPlayer();
        });
    }

    handleMessage(event) {
        const data = event.data;

        if (data.event === 'play') {
            this.playing = true;
            this.startViewRecord();
        } else if (data.event === 'pause') {
            this.playing = false;
            this.pauseViewRecord();
        } else if (data.event === 'ended') {
            this.endVideo();
        } else if (data.event === 'timeupdate') {
            this.currentTime = data.currentTime;
        }
    }

    async removePlayer() {
        if (this.ready === false) {
            return false;
        }
        this.player.remove();
        this.resetPlayerValues();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            return false;
        }
        this.player.contentWindow.postMessage({ method: 'play' }, '*');
        this.playing = true;
        return true;
    }

    async togglePause() {
        if (this.ready === false) {
            return false;
        }
        this.player.contentWindow.postMessage({ method: 'pause' }, '*');
        this.playing = false;
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        return this.currentTime;
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        return this.playing;
    }
}
