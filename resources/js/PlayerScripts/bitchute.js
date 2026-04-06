import Player from './player.js';

/**
 * BitChute iframe embed. external_id must be the video id (same as /video/{id}/).
 * Controls are best-effort: cross-origin iframe API is not documented.
 */
export default class BitChutePlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then(() => {
            const iframe = document.createElement('iframe');
            iframe.id = `bitchute-player-${this.external_id}`;
            const ap = this.autoplay ? 1 : 0;
            iframe.src = `https://www.bitchute.com/embed/${encodeURIComponent(this.external_id)}/?autoplay=${ap}`;
            iframe.setAttribute('allowfullscreen', 'true');
            iframe.allow = 'autoplay; fullscreen';
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.border = 'none';

            this.playerDiv.appendChild(iframe);
            this.player = iframe;

            iframe.onload = () => {
                this.ready = true;
                this.playing = !!this.autoplay;
                if (this.autoplay) {
                    this.togglePlay();
                } else {
                    this.togglePause();
                }
            };

            this.createPlayer();
        });
    }

    async removePlayer() {
        if (this.player) {
            this.player.remove();
            this.player = null;
        }
        this.resetPlayerValues();
        return true;
    }

    async togglePlay() {
        if (!this.ready) {
            return false;
        }
        this.playing = true;
        return true;
    }

    async togglePause() {
        if (!this.ready) {
            return false;
        }
        this.playing = false;
        return true;
    }

    async getCurrentPosition() {
        if (!this.ready) {
            return false;
        }
        return Math.floor(this.currentTime ?? this.start_time ?? 0);
    }

    async isPlaying() {
        if (!this.ready) {
            return false;
        }
        return this.playing;
    }
}
