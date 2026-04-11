import { defineStore } from 'pinia';

export const usePodcastPlayerStore = defineStore('PodcastPlayer', {
    state: () => ({
        podcast: null,
        episode: null,
        isPlaying: false,
        currentTime: 0,
        duration: 0,
        pendingSeek: null,
    }),
    getters: {
        hasTrack(state) {
            return !!(state.podcast && state.episode && state.episode.audio_url);
        },
        artUrl(state) {
            return state.episode?.thumbnail_url || state.podcast?.thumbnail_url || null;
        },
        showFloating(state) {
            if (!state.podcast || !state.episode?.audio_url) return false;
            try {
                if (typeof route === 'undefined') return true;
                if (!route().current('podcast.episode')) return true;
                const p = route().params;
                return p.podcastSlug !== state.podcast?.slug || p.episodeSlug !== state.episode?.slug;
            } catch {
                return true;
            }
        },
    },
    actions: {
        /**
         * @param {object} podcast — slug, title, thumbnail_url, creator optional
         * @param {object} episode — slug, title, audio_url, thumbnail_url?, duration?, time_published?
         * @param {{ autoplay?: boolean }} options
         */
        playTrack(podcast, episode, options = {}) {
            const autoplay = options.autoplay !== false;
            this.podcast = podcast
                ? {
                      slug: podcast.slug,
                      title: podcast.title,
                      thumbnail_url: podcast.thumbnail_url,
                      creator: podcast.creator ?? null,
                  }
                : null;
            this.episode = episode
                ? {
                      slug: episode.slug,
                      title: episode.title,
                      audio_url: episode.audio_url,
                      thumbnail_url: episode.thumbnail_url || podcast?.thumbnail_url,
                      duration: episode.duration,
                      time_published: episode.time_published,
                  }
                : null;
            if (autoplay) this.isPlaying = true;
        },
        togglePlay() {
            if (!this.hasTrack) return;
            this.isPlaying = !this.isPlaying;
        },
        pause() {
            this.isPlaying = false;
        },
        play() {
            if (!this.hasTrack) return;
            this.isPlaying = true;
        },
        requestSeek(seconds) {
            if (!Number.isFinite(seconds) || seconds < 0) return;
            this.pendingSeek = seconds;
        },
        consumePendingSeek() {
            const t = this.pendingSeek;
            this.pendingSeek = null;
            return t;
        },
        syncTimeState(currentTime, duration) {
            this.currentTime = currentTime;
            if (duration != null && Number.isFinite(duration) && duration > 0) {
                this.duration = duration;
            }
        },
        clear() {
            this.podcast = null;
            this.episode = null;
            this.isPlaying = false;
            this.currentTime = 0;
            this.duration = 0;
            this.pendingSeek = null;
        },
    },
});
