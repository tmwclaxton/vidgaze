


// make a constructor function for the player
import {useAuthStore} from "@/Stores/AuthStore";
import axios from "axios";
import {useQueueStore} from "@/Stores/QueueStore";
import {usePlayerStore} from "@/Stores/PlayerStore";
import {v4 as uuidv4} from "uuid";

export default class Player {
    built = false;
    playing = false;
    ready = false;
    player = null;
    endScreen = false;
    endScreenNext = null;
    isViewRecording = false;
    viewRecordTimer = null;
    viewRecordDuration = 0;

    constructor(object, playerDiv, start_time = 0, autoplay = false, checkHistoryTime = false, short = false) {
        this.seeked = false;
        this.object = object
        this.playerDiv = playerDiv;
        this.external_id = object.external_id;
        this.autoplay = autoplay;
        this.checkHistoryTime = checkHistoryTime;
        this.start_time = start_time;
        this.currentTime = this.start_time;
        this.short = short;
        /** @type {ReturnType<typeof setTimeout> | null} */
        this._shortApproxEndTimer = null;
    }

    /**
     * Iframe embeds without a documented "ended" API (Odysee, BitChute): advance when duration elapses.
     */
    scheduleShortApproximateEnd() {
        this.clearShortApproximateEndTimer();
        if (!this.short) {
            return;
        }
        const sec = Number(this.object?.duration);
        if (!Number.isFinite(sec) || sec <= 0) {
            return;
        }
        const ms = Math.min(Math.max((sec + 2) * 1000, 5000), 60 * 60 * 1000);
        this._shortApproxEndTimer = setTimeout(() => {
            this._shortApproxEndTimer = null;
            this.endVideo();
        }, ms);
    }

    clearShortApproximateEndTimer() {
        if (this._shortApproxEndTimer != null) {
            clearTimeout(this._shortApproxEndTimer);
            this._shortApproxEndTimer = null;
        }
    }

    createPlayer() {
        this.built = true;
        this.endScreen = false;
        this.viewRecordDuration = 0;
    }

    resetPlayerValues() {
        this.clearShortApproximateEndTimer();
        // usePlayerStore().endVideo(this.external_id);
        this.built = false;
        this.playing = false;
        this.player = null;
        this.ready = false;
        this.endScreen = true;
        this.isViewRecording = false;
    }

    // get the start time of the video by checking the history
    async getStartTimePlayer() {
        if (useAuthStore().user !== null && this.checkHistoryTime) {
            // get the view history for this video and set the start time to the last time they watched it
            try {
                const response = await axios.get(route('api.video.interaction', {video_id: this.object.id}));
                const data = response.data;
                if (data !== undefined && data.interaction !== null) {
                    this.start_time = data.interaction.view_point;
                }
            } catch (error) {
                console.log(error);
                this.start_time = 0;
            }
        }
    }

    endVideo() {
        console.log('end view record: ' + this.external_id);
        this.endScreen = true;
        this.playing = false;
        // random string to force the player to re-render
        useQueueStore().refreshMiniPlayer = Math.random().toString(36).substring(7);
        this.stopViewRecord();

        // Shorts feed: advance to next item (handled on Shorts page via PlayerStore callback).
        if (this.short) {
            usePlayerStore().notifyShortVideoEnded(this.external_id);
            return;
        }

        // if the mini player is showing
        if (useQueueStore().showMiniPlayer) {
            // check if queue has an item after this one
            // wait 1 - as if we are deleting the item from the queue it will take a second to update
            if (useQueueStore().items.length > useQueueStore().index + 1) {
                useQueueStore().changeIndex(useQueueStore().index + 1);
            } else {
                this.removePlayer();
            }
            return;
        }

        // if watch page
        // if (useQueueStore().items.length > useQueueStore().index + 1) {
            // if we change index here then we can't do a countdown in the end screen
        // }
        this.removePlayer();
        usePlayerStore().refreshFrontEndComponent = Math.random().toString(36).substring(7);


    }

    startViewRecord() {
        if (this.isViewRecording) {
            console.log('STARTVIEWRECORD: Error: View recording already started: ' + this.external_id);
            return;
        }
        // pause all other players in player store
        for (const player of usePlayerStore().players) {
            if (player.external_id !== this.external_id && player.ready) {
                player.togglePause();
            }
        }

        // console.log('start view record' + this.external_id);
        const interval = 2.5;
        this.isViewRecording = true;
        const uuid = uuidv4();
        this.viewRecordTimer = setInterval(async () => {
            try {
                // check if the player is playing
                const isPlaying = await this.isPlaying();
                if (!isPlaying) {
                    clearInterval(this.viewRecordTimer);
                    this.isViewRecording = false;
                    console.log('STARTVIEWRECORD: Error: Player is not playing' + this.external_id + ' resetting view record');
                    return;
                }
                const viewPoint = await this.getCurrentPosition();
                this.viewRecordDuration += interval;
                const viewPointNum = Number(viewPoint);
                // check if we have all the data we need to record the view (0 is a valid position)
                if (!this.object.id || !this.object.item_type || !this.viewRecordDuration || !Number.isFinite(viewPointNum)) {
                    this.isViewRecording = false;
                    clearInterval(this.viewRecordTimer);
                    console.log("missing data to record view: " + this.external_id + ' resetting view record');
                    return;
                }
                    //using ziggy to get the view record route view.listener
                await axios.post(route('api.view.listener'), {
                    item_id: this.object.id,
                    type: this.object.item_type,
                    watch_duration: parseInt(this.viewRecordDuration),
                    view_point: parseInt(viewPointNum, 10),
                    client_identifier: uuid
                });
            } catch (error) {
                console.log('STARTVIEWRECORD: Error: ' + error + ' resetting view record');
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
        }, interval * 1000);
    }


    pauseViewRecord() {
        // console.log('pause view record: ' + this.external_id);
        if (this.isViewRecording) {
            this.isViewRecording = false;
            clearInterval(this.viewRecordTimer);
        }

    }

    stopViewRecord() {
        // console.log('stop view record: ' + this.external_id);
        if (this.isViewRecording) {
            this.isViewRecording = false;
            clearInterval(this.viewRecordTimer);
        }
    }

    playLock = null;
    static SAFE_OP_MAX_RETRIES = 30;

    safeTogglePlay(retries = 0) {
        this.playLock = "play";
        if (usePlayerStore().scriptsLoaded && this.built && this.ready) {
            this.togglePlay();
            this.playLock = null;
            return;
        }
        if (retries >= Player.SAFE_OP_MAX_RETRIES) {
            this.playLock = null;
            return;
        }
        setTimeout(() => {
            if (this.playLock !== "pause") {
                this.safeTogglePlay(retries + 1);
            }
        }, 1000);
    }

    safeTogglePause(retries = 0) {
        this.playLock = "pause";
        if (usePlayerStore().scriptsLoaded && this.built && this.ready) {
            this.togglePause();
            this.playLock = null;
            return;
        }
        if (retries >= Player.SAFE_OP_MAX_RETRIES) {
            this.playLock = null;
            return;
        }
        setTimeout(() => {
            if (this.playLock !== "play") {
                this.safeTogglePause(retries + 1);
            }
        }, 1000);
    }

    safeRemovePlayer(retries = 0) {
        if (this.player === null ) {
            return;
        }
        if (usePlayerStore().scriptsLoaded && this.ready) {
            this.removePlayer();
            return;
        }
        if (retries >= Player.SAFE_OP_MAX_RETRIES) {
            console.log('safeRemovePlayer: max retries', this.external_id);
            return;
        }
        setTimeout(() => {
            this.safeRemovePlayer(retries + 1);
        }, 1000);
    }

}
