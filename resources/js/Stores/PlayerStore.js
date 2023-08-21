import {defineStore} from 'pinia'
import YouTubePlayer from "../PlayerScripts/youtube.js";
import VimeoPlayer from "@/PlayerScripts/vimeo";
import TwitchPlayer from "@/PlayerScripts/twitch";
import DailymotionPlayer from "@/PlayerScripts/dailymotion";
import {loadScript} from "vue-plugin-load-script";
export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            scriptsLoaded: false,
            players: [],
        }
    },
    getters: {
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
            console.log('loading player scripts');
            await this.loadScript('https://geo.dailymotion.com/libs/player/xiohs.js', 'dailymotion-api')
            await this.loadScript('https://www.youtube.com/iframe_api', 'youtube-api');
            await this.loadScript('https://player.vimeo.com/api/player.js', 'vimeo-api');
            await this.loadScript('https://player.twitch.tv/js/embed/v1.js', 'twitch-api');
        },

        isScriptLoaded(platform) {
            switch (platform) {
                case 'YouTube':
                    return window.YT !== undefined && window.YT.Player !== undefined;
                case 'Vimeo':
                    return Vimeo !== undefined;
                case 'Twitch':
                    return Twitch !== undefined;
                case 'Dailymotion':
                    return dailymotion !== undefined;
            }
        },


        async buildPlayer(playerDivHolderID = null, object, startTime = 0, autoplay = false, checkViewHistoryStartTime = true, short = false) {

            // check if player already exists
            const existingPlayer = this.findPlayer(object.external_id);
            if (existingPlayer !== null && existingPlayer.short === true) {
                return;
            }

            // console.log('building player for object: ', object);
            this.endScreen = false; // for watch page
            this.show = true; // for mini player

            // until scriptsLoaded is true wait 1 second and try again
            if (!this.isScriptLoaded(object.preferred_source)) {
                await this.loadScripts(); // don't worry about this running multiple times, it checks if the script by id exists before trying to add it again
                setTimeout(() => {
                    console.log('scripts not loaded yet, trying again in 2 second')
                    this.buildPlayer(playerDivHolderID, object, startTime, autoplay, checkViewHistoryStartTime);
                }, 2000);
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

            // check if the start time is within 60 seconds of the end of the video and if so set it to 0
            // if this doesn't seem to be working, just double check with db that the duration is set correctly
            if (object.duration - startTime < 60) {
                startTime = 0;
            }

            if (existingPlayer) {
                console.log('player already exists');
                existingPlayer.playerDiv = playerDiv;
                existingPlayer.endScreen = false;
                existingPlayer.checkHistoryTime = checkViewHistoryStartTime;
                existingPlayer.start_time = startTime;
                existingPlayer.create();
                return;
            } else {
                let player = null;
                // // create player
                switch (object.preferred_source) {
                    case "YouTube":
                        player = await new YouTubePlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime, short);
                        break;
                    case "Vimeo":
                        player = await new VimeoPlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime, short);
                        break;
                    case "Dailymotion":
                        player = await new DailymotionPlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime, short);
                        break;
                    case "Twitch":
                        player = await new TwitchPlayer(object, playerDiv, startTime, autoplay, false, short);
                        break;
                    default:
                        console.log("ERROR: preferred source not found");
                        return;
                }
                await player.create().then(() => {
                    // add player to players array
                    this.players.push( player );
                });
            }
        },


        findPlayer(external_id) {
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].external_id === external_id) {
                    return this.players[i];
                }
            }
            return false;
        },

        async destroyPlayers(fullDestroy = false, shorts = false) {
            // if shorts is true player.shorts should be true and only those players will be destroyed
            this.players.forEach(player => {
                this.destroyPlayer( player.external_id, fullDestroy, shorts );
            });
        },

        async destroyPlayer(external_id, fullDestroy = false, shorts = false) {
            const player = this.findPlayer(external_id);
            if (player) {
                player.removePlayer();
            }
            if (fullDestroy) {
                this.players = this.players.filter(player => player.external_id !== external_id && player.shorts !== shorts);
            }
        },

    }
})
