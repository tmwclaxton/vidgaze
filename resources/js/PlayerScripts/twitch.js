// import player.js
import Player from './player.js';
import {toRaw} from "vue";

/** Domains allowed to embed the Twitch player (must match every host users hit). */
const TWITCH_PARENT_HOSTS = Object.freeze([
    'localhost',
    '127.0.0.1',
    'vidgaze.tv',
    'www.vidgaze.tv',
    'www.staging.vidgaze.tv',
    'staging.vidgaze.tv',
]);

export default class TwitchPlayer extends Player {
    /**
     * Twitch expects VOD ids as "v123..."; numeric-only ids get a v prefix.
     * Otherwise treat external_id as a live channel login.
     */
    static normalizeTwitchVideoId(externalId) {
        const id = String(externalId).trim();
        const m = id.match(/^v?(\d+)$/i);
        if (m) {
            return `v${m[1]}`;
        }
        return null;
    }

    /** Twitch embed `time` param: "0h3m40s" per https://dev.twitch.tv/docs/embed/video-and-clips/ */
    static secondsToTwitchTime(totalSeconds) {
        const s = Math.max(0, Math.floor(Number(totalSeconds) || 0));
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return `${h}h${m}m${sec}s`;
    }

    buildEmbedOptions() {
        const options = {
            parent: [...TWITCH_PARENT_HOSTS],
            width: '100%',
            height: '100%',
            autoplay: Boolean(this.autoplay),
            controls: true,
        };

        const videoId = TwitchPlayer.normalizeTwitchVideoId(this.external_id);
        if (videoId) {
            options.video = videoId;
            const start = Math.max(0, Math.floor(Number(this.start_time) || 0));
            if (start > 0) {
                options.time = TwitchPlayer.secondsToTwitchTime(start);
            }
        } else {
            options.channel = String(this.external_id).trim();
        }

        return options;
    }

    async create() {
        await this.getStartTimePlayer();

        const options = this.buildEmbedOptions();
        this.player = new Twitch.Player(this.playerDiv, options);

        this.player.addEventListener(Twitch.Player.PLAYING, () => {
            this.playing = true;
            this.startViewRecord();
        });

        this.player.addEventListener(Twitch.Player.PAUSE, () => {
            this.playing = false;
            this.pauseViewRecord();
        });

        this.player.addEventListener(Twitch.Player.ENDED, () => {
            this.endVideo();
        });

        this.player.addEventListener(Twitch.Player.READY, () => {
            this.ready = true;
        });

        this.createPlayer();
    }

    async removePlayer() {
        if (this.player) {
            try {
                toRaw(this.player).destroy();
            } catch (e) {
                console.log('TwitchPlayer remove:', e);
            }
            this.player = null;
        }
        this.resetPlayerValues();
        return true;
    }

    async togglePlay() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).play();
        this.playing = true;
        return true;
    }

    async togglePause() {
        if (this.ready === false) {
            return false;
        }
        await toRaw(this.player).pause();
        this.playing = false;
        return true;
    }

    async getCurrentPosition() {
        if (this.ready === false) {
            return false;
        }
        try {
            const t = await toRaw(this.player).getCurrentTime();
            if (typeof t === 'number' && !Number.isNaN(t)) {
                this.currentTime = t;
                return t;
            }
        } catch (e) {
            /* live streams: getCurrentTime not supported */
        }
        return Math.floor(this.currentTime ?? this.start_time ?? 0);
    }

    async isPlaying() {
        if (this.ready === false) {
            return false;
        }
        this.playing = !await toRaw(this.player).isPaused();
        return this.playing;
    }
}
