import Player from './player.js';

/**
 * Odysee iframe embed. external_id must be the permanent embed id (Share → <> on odysee.com),
 * used in https://odysee.com/$/embed/{id}
 * Controls are best-effort: cross-origin iframe API is not documented.
 */
export default class OdyseePlayer extends Player {
    async create() {
        await this.getStartTimePlayer().then(() => {
            const iframe = document.createElement('iframe');
            iframe.id = `odysee-player-${this.external_id}`;
            const ap = this.autoplay ? 1 : 0;
            iframe.src = `https://odysee.com/$/embed/${encodeURIComponent(this.external_id)}?autoplay=${ap}`;
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
                this.scheduleShortApproximateEnd();
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
