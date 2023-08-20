import {defineStore} from 'pinia'
import YouTubePlayer from "../PlayerScripts/youtube.js";
import VimeoPlayer from "@/PlayerScripts/vimeo";
import TwitchPlayer from "@/PlayerScripts/twitch";
import DailymotionPlayer from "@/PlayerScripts/dailymotion";
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
            await this.loadScript('https://geo.dailymotion.com/libs/player/xfjc3.js', 'dailymotion-api')
            await this.loadScript('https://www.youtube.com/iframe_api', 'youtube-api');
            await this.loadScript('https://player.vimeo.com/api/player.js', 'vimeo-api');
            await this.loadScript('https://player.twitch.tv/js/embed/v1.js', 'twitch-api');
            this.scriptsLoaded = true
        },


        async buildPlayer(playerDivHolderID = null, object, startTime = 0, autoplay = false, checkViewHistoryStartTime = true) {
            // console.log('building player for object: ', object);
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

            // check if player already exists
            const existingPlayer = this.findPlayer(object.external_id);
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
                        player = await new YouTubePlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime);
                        break;
                    case "Vimeo":
                        player = await new VimeoPlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime);
                        break;
                    case "Dailymotion":
                        player = await new DailymotionPlayer(object, playerDiv, startTime, autoplay, checkViewHistoryStartTime);
                        break;
                    case "Twitch":
                        player = await new TwitchPlayer(object, playerDiv, startTime, autoplay);
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

        async destroyPlayers(fullDestroy = false) {
            // iterate through players and get object external_id and destroy div using that as id
            this.players.forEach(player => {
                player.removePlayer()
            });

            if (fullDestroy) {
                this.players = [];
            }
        },

        async destroyPlayer(external_id, fullDestroy = false) {
            const player = this.findPlayer(external_id);
            if (player) {
                player.removePlayer();
            }
            if (fullDestroy) {
                this.players = this.players.filter(player => player.object.external_id !== external_id);
            }
        },

    }
})
