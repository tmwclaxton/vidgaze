


// make a constructor function for the player
import {useAuthStore} from "@/Stores/AuthStore";
import axios from "axios";
import {useQueueStore} from "@/Stores/QueueStore";
import {usePlayerStore} from "@/Stores/PlayerStore";
import {v4 as uuidv4} from "uuid";

export default class Player {
    object = null;
    playerDiv = null; // the div / div id that will hold the player
    source = null; // platform
    playing = false; // is the player playing
    playerDivHolderID = null; // the id of the div that will hold the player
    external_id = null; // the id of the video on the platform
    autoplay = false;
    checkHistoryTime = false; // should we check the history to see if we have a start time
    start_time = 0;
    currentTime = 0;
    ready = false; // is the player ready to play
    player = null; // the player object
    built = false; // is the player built
    // locked = false; // is the player locked i.e. a call to play or pause is in progress
    endScreen = false; // is the player on the end screen
    isViewRecording = false; // this is true when we are recording the view of a video
    viewRecordTimer = null;// the timer that is running to record the view
    viewRecordDuration = 0;// this is the total time spent watching the video

    constructor(object, playerDiv, start_time = 0, autoplay = false, checkHistoryTime = false) {
        this.built = false;
        this.object = object
        this.playing = false;
        this.playerDiv = playerDiv;
        this.external_id = object.external_id;
        this.autoplay = autoplay;
        this.checkHistoryTime = checkHistoryTime;
        this.start_time = start_time;
        this.currentTime = this.start_time;
        this.ready = false;
        this.player = null;
        this.endScreen = false;
        this.isViewRecording = false;
        this.viewRecordTimer = null;
        this.viewRecordDuration = 0;
    }

    createPlayer() {
        this.built = true;
        this.endScreen = false;
    }

    destroyPlayer() {
        // usePlayerStore().endVideo(this.external_id);
        this.built = false;
        this.playing = false;
        this.player = null;
        this.ready = false;
        this.endScreen = true;
    }

    playPlayer() {
        this.playing = true;
    }

    pausePlayer() {
        this.playing = false;
    }

    // get the start time of the video by checking the history
    async getStartTimePlayer() {
        if (useAuthStore().user === null || this.checkHistoryTime === false) {
            return;
        }
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

    endVideo() {
        this.stopViewRecord();
        console.log('end view record: ' + this.external_id);
        if (!usePlayerStore().shortsPage) {
            // check if queue has an item after this one
            // wait 1 - as if we are deleting the item from the queue it will take a second to update
            if (useQueueStore().items.length > useQueueStore().index + 1) {
                useQueueStore().changeIndex(useQueueStore().index + 1);
            } else {
                this.player.remove();
            }
        }
    }

    startViewRecord() {
        if (this.isViewRecording) {
            console.log('STARTVIEWRECORD: Error: View recording already started');
            return;
        }

        console.log('start view record' + this.external_id);
        const interval = 2.5;
        this.isViewRecording = true;
        const uuid = uuidv4();
        this.viewRecordTimer = setInterval(async () => {
            try {
                const isPlaying = await this.isPlaying();
                if (!isPlaying) {
                    clearInterval(this.viewRecordTimer);
                    console.log('STARTVIEWRECORD: Error: Player is not playing');
                    return;
                }
                if (!this.isViewRecording) {
                    clearInterval(this.viewRecordTimer);
                    console.log('STARTVIEWRECORD: Error: View recording was stopped');
                    return
                }

                const viewPoint = await this.getCurrentPosition();
                this.viewRecordDuration += interval;
                if (!(this.object.id && this.object.type && this.viewRecordDuration && viewPoint)) {
                    console.log("missing data to record view");
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
                console.log('STARTVIEWRECORD: Error: ' + error);
                clearInterval(this.viewRecordTimer);
            }
        }, interval * 1000);
    }


    pauseViewRecord() {
        console.log('pause view record: ' + this.external_id);
        if (this.isViewRecording) {
            this.isViewRecording = false;
            clearInterval(this.viewRecordTimer);
        }

    }

    stopViewRecord() {
        console.log('stop view record: ' + this.external_id);
        if (this.isViewRecording) {
            this.isViewRecording = false;
            clearInterval(this.viewRecordTimer);
        }
        this.viewRecordDuration = 0;

        this.endScreen = true;
    }

}
