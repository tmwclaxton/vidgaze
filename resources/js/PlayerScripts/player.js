


// make a constructor function for the player
import {useAuthStore} from "@/Stores/AuthStore";
import axios from "axios";
import {useQueueStore} from "@/Stores/QueueStore";

export default class Player {
    object = null;
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

    constructor(object, playerDiv, start_time = 0, autoplay = false, checkHistoryTime = false) {
        this.built = false;
        this.object = object
        this.playing = false;
        this.playerDiv = playerDiv;
        this.external_id = object.external_id;
        this.autoplay = autoplay;
        this.checkHistoryTime = checkHistoryTime;
        this.start_time = start_time;
        if (checkHistoryTime) {
            this.start_time = this.getStartTimePlayer();
        }
        this.currentTime = this.start_time;
        this.ready = false;
        this.player = null;
    }

    createPlayer() {
        this.built = true;
    }

    destroyPlayer() {
        this.built = false;
        this.playing = false;
        this.player = null;
        this.currentTime = 0;
    }

    playPlayer() {
        this.playing = true;
    }

    pausePlayer() {
        this.playing = false;
    }

    // get the start time of the video by checking the history
    async getStartTimePlayer() {
        if (useAuthStore().user === null) {
            return 0;
        }
        // get the view history for this video and set the start time to the last time they watched it
        try {
            const response = await axios.get(route('api.video.interaction', {video_id: this.external_id}));
            const data = response.data;
            if (data !== undefined && data.view_point !== null) {
                return data.view_point;
            }
        } catch (error) {
            console.log(error);
            return 0;
        }
    }

    endVideo() {

    }


}
