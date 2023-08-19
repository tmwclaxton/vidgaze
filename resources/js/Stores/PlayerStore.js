import {defineStore} from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";
import {usePage} from "@inertiajs/vue3";
import axios from "axios";
import YouTubePlayer from "../PlayerScripts/youtube.js";
import VimeoPlayer from "@/PlayerScripts/vimeo";
import TwitchPlayer from "@/PlayerScripts/twitch";
import DailymotionPlayer from "@/PlayerScripts/dailymotion";
import { v4 as uuidv4 } from 'uuid';
export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            scriptsLoaded: false, // this is true when the scripts have been loaded
            players: [],
            isViewRecording: false,  // this is true when we are recording the view of a video
            viewRecordTimer: null, // the timer that is running to record the view
            viewRecordDuration: 0, // this is the total time spent watching the video
            endScreen: false
        }
    },
    getters: {
        showMiniPlayer() {
            // Compute the value of showMiniPlayer based on your logic
            // For example, you can check if players array is not empty
            let queueStore = useQueueStore();
            // also depends on what page you are on ... // url doesn't contian shorts or watch
            return queueStore.items !== undefined && queueStore.items.length > 0 && usePage().url !== '/shorts' && !route().current('watch.show');
        },
        shortsPage() {
            // use ziggy to check if we are on the shorts page
            return route().current('videos.shorts');
        },
    },

    actions: {
        async loadScript(src, id)  {
            if (!document.getElementById(id)) {
                const tag = document.createElement('script');
                tag.src = src;
                tag.id = id;
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }
        },

        async loadScripts() {
            await this.loadScript('https://geo.dailymotion.com/libs/player/xfjc3.js', 'dailymotion-api')
            await this.loadScript('https://www.youtube.com/iframe_api', 'youtube-api');
            await this.loadScript('https://player.vimeo.com/api/player.js', 'vimeo-api');
            await this.loadScript('https://player.twitch.tv/js/embed/v1.js', 'twitch-api');
            this.scriptsLoaded = true
        },


        async buildPlayer(playerDivHolderID = null, object, startTime = 0, autoplay = false, checkViewHistoryStartTime = true) {
            this.endScreen = false; // for watch page
            this.show = true; // for mini player

            // until scriptsLoaded is true wait 1 second and try again
            if (!this.scriptsLoaded) {
                await this.loadScripts(); // don't worry about this running multiple times, it checks if the script by id exists before trying to add it again
                setTimeout(() => {
                    console.log('scripts not loaded yet, trying again in 2 second')
                    this.buildPlayer(playerDivHolderID, object, startTime, autoplay, checkViewHistoryStartTime);
                }, 2000);
                return;
            }

            // check if player already exists
            if (this.findPlayer(object.external_id)) {
                console.log('player already exists');
                return;
            }

            // get the div holder
            let playerDivHolder = document.getElementById(playerDivHolderID);

            if(playerDivHolder === null){
                return;
            }

            //remove all children of player_div_holder
            while (playerDivHolder.firstChild) {
                playerDivHolder.removeChild(playerDivHolder.firstChild);
            }

            // if div with id of object.external_id exists remove it
            if (document.getElementById(object.external_id)) {
                document.getElementById(object.external_id).remove();
            }

            // // create player_div element inside player_div_holder
            let playerDiv = playerDivHolder.appendChild(document.createElement('div'));
            if (playerDiv === null) {
                console.log('CATASTROPHIC ERROR!!!! a playerDiv could be not found for this player with id: ' + object.external_id);
                return;
            }
            playerDiv.id = object.external_id;
            playerDiv.classList.add('h-full');
            playerDiv.classList.add('w-full');

            let player = null;
            // // create player
            switch (object.preferred_source) {
                case "YouTube":
                    playerDiv.removeAttribute('style');
                    player = await new YouTubePlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime);
                    break;
                case "Vimeo":
                    object.external_id = "855016876"
                    playerDiv.removeAttribute('style');
                    player = await new VimeoPlayer(object, playerDiv.id, startTime, autoplay, checkViewHistoryStartTime);
                    break;
                case "Dailymotion":
                    playerDiv.removeAttribute('style');
                    object.external_id = "x8n4xse";
                    player = await new DailymotionPlayer(object, playerDiv.id, startTime, autoplay, checkViewHistoryStartTime);
                    break;
                case "Twitch":
                    player = await new TwitchPlayer(object, playerDiv, startTime, autoplay);
                default:
                    console.log("ERROR: preferred source not found");
            }
            player.create();

            // // add player to players array
            this.pushPlayer(player);
        },

        pushPlayer(player) {
            // check if player is already in players array
            if (this.findPlayer(player.object.external_id)) {
                console.log('player already in array')
                return;
            }

            //create player and add to players array
            this.players.push( player );

        },

        findPlayer(external_id) {
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].external_id === external_id) {
                    return this.players[i];
                }
            }
            return false;
        },

        async destroyPlayers() {
            // iterate through players and get object external_id and destroy div using that as id
            this.players.forEach(player => {
                player.player.remove();
            });

            this.stopViewRecord();
            this.players = [];
        },

        endVideo(external_id) {
            this.stopViewRecord();
            console.log('end view record' + external_id);
            if (!this.shortsPage) {
                // check if queue has an item after this one
                let queueStore = useQueueStore();

                // wait 1 - as if we are deleting the item from the queue it will take a second to update
                if (queueStore.items.length > queueStore.index + 1) {
                    queueStore.changeIndex(queueStore.index + 1);
                } else {
                    const player = this.findPlayer(external_id);
                    player.remove();
                }
            }
        },

        startViewRecord(external_id) {
            console.log('start view record' + external_id);

            const interval = 2.5;
            if (!this.isViewRecording) {
                this.isViewRecording = true;
                let player = this.findPlayer(external_id);

                // pause any current players
                // iterate through players except the one we are starting
                this.players.filter(player => player.object.external_id !== external_id).forEach(item => {
                    player.togglePause();
                });

                const uuid = uuidv4();
                this.viewRecordTimer = setInterval(async () => {
                    try {
                        const isPlaying = await player.isPlaying();
                        if (isPlaying && this.players.length > 0) {
                            const viewPoint = await player.getCurrentPosition();
                            this.viewRecordDuration += interval;
                            if (player.object.id && player.object.type && this.viewRecordDuration && viewPoint) {
                                //using ziggy to get the view record route view.listener
                                axios.post(route('api.view.listener'), {
                                    item_id: player.object.id,
                                    type: player.object.type,
                                    watch_duration: this.viewRecordDuration,
                                    view_point: viewPoint,
                                    client_identifier: uuid
                                });
                            } else {
                                console.log("missing data to record view");
                            }
                        } else {
                            console.log('STARTVIEWRECORD: Error: Player is not playing');
                            clearInterval(this.viewRecordTimer);
                        }
                    } catch (error) {
                        console.log('STARTVIEWRECORD: Error: ' + error);
                        clearInterval(this.viewRecordTimer);
                    }
                }, interval * 1000);
            }
        },


        pauseViewRecord(external_id = null) {
            console.log('pause view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }

        },

        stopViewRecord(external_id = null) {
            console.log('stop view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
            this.viewRecordDuration = 0;

            this.endScreen = true;
        },


    }
})
