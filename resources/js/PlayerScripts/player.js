


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
    }

    createPlayer() {
        this.built = true;
        this.endScreen = false;
        this.viewRecordDuration = 0;
    }

    resetPlayerValues() {
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

        // if a short
        if (this.short) {
            this.togglePlay();
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
                // check if we have all the data we need to record the view
                if (!(this.object.id && this.object.type && this.viewRecordDuration && viewPoint)) {
                    this.isViewRecording = false;
                    clearInterval(this.viewRecordTimer);
                    console.log("missing data to record view: " + this.external_id + ' resetting view record');
                    return;
                }
                    //using ziggy to get the view record route view.listener
                await axios.post(route('api.view.listener'), {
                    item_id: this.object.id,
                    type: this.object.type,
                    watch_duration: parseInt(this.viewRecordDuration),
                    view_point: parseInt(viewPoint),
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

    safeTogglePlay() {
        if (usePlayerStore().scriptsLoaded && this.built && this.ready) {
            this.togglePlay();
        } else {
            setTimeout(() => {
                this.safeTogglePlay();
            }, 1000);
        }
    }

    safeTogglePause() {
        if (usePlayerStore().scriptsLoaded && this.built && this.ready) {
            this.togglePause();
        } else {
            setTimeout(() => {
                this.safeTogglePause();
            }, 1000);
        }
    }

}
