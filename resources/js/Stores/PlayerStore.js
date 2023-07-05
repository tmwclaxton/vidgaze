import {defineStore} from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";
import {toRaw} from "vue";
import {usePage} from "@inertiajs/vue3";
import axios from "axios";
export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            debug: true,

            scriptsLoaded: false, // this is true when the scripts have been loaded
            players: [], //in the form of [[type => "video", object => {id: 1, ...}, player => player_object], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            isViewRecording: false,  // this is true when we are recording the view of a video
            viewRecordTimer: null, // the timer that is running to record the view
            viewRecordDuration: 0, // this is the total time spent watching the video
            currentTimePosition: 0, // i.e. 0 seconds into the video or 45 seconds into the video, this is the current time position of the video this helps when switching between watch page and mini player
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
        debugMessage(message) {
            if (this.debug) {
                console.log(message);
            }
        },



        endVideo(external_id) {
            this.stopViewRecord();

            if (this.showMiniPlayer) {
                // check if queue has an item after this one
                let queueStore = useQueueStore();

                // wait 1 - as if we are deleting the item from the queue it will take a second to update
                if (queueStore.items.length > queueStore.index + 1) {
                    queueStore.changeIndex(queueStore.index + 1);
                    // this.debugMessage('STOPVIEWRECORD mini player next item');
                } else {
                    const player = this.findPlayer(external_id);
                    this.destroyItem(player);
                }
            } else if (this.shortsPage) {
                // destroy player
                // const player = this.findPlayer(external_id);
                // this.destroyItem(player).then(r => {
                //    scroll to next video
                //
                // });
            }



            this.debugMessage('end video' + external_id);
        },

        startViewRecord(external_id) {
            const interval = 2.5;
            this.debugMessage('start view record');
            console.log(external_id);
            if (!this.isViewRecording) {
                this.isViewRecording = true;
                let player = this.findPlayer(external_id);

                this.viewRecordTimer = setInterval(async () => {
                    try {
                        const isPlaying = await this.isPlayerPlaying(player);
                        if (isPlaying) {
                            this.viewRecordDuration += interval;
                            this.debugMessage('STARTVIEWRECORD: View Record Duration: ' + this.viewRecordDuration);

                            // console.log(
                            //     [
                            //         player.object.id,
                            //         player.object.type,
                            //         this.viewRecordDuration,
                            //         this.currentTimePosition
                            //
                            //     ]
                            // );

                            // check no values are null
                            if (player.object.id && player.object.type && this.viewRecordDuration && this.currentTimePosition) {
                                //using ziggy to get the view record route view.listener
                                axios.post(route('view.listener'), {
                                    item_id: player.object.id,
                                    type: player.object.type,
                                    watch_duration: this.viewRecordDuration,
                                    view_point: this.currentTimePosition
                                });
                            } else {
                                console.log('null values');
                            }


                        } else {
                            this.debugMessage('STARTVIEWRECORD: Error: Player is not playing');
                            clearInterval(this.viewRecordTimer);

                        }
                    } catch (error) {
                        this.debugMessage('STARTVIEWRECORD: Error: ' + error);
                        clearInterval(this.viewRecordTimer);
                    }
                }, interval * 1000);
            }
        },


        // has to be async as we need to wait for the Dailymotion player to be ready
        async isPlayerPlaying(player) {
            this.debugMessage('is player playing');

            if (!player) {
                this.debugMessage('Player not found');
                throw new Error('Player not found');
            }

            let playing = false;

            if (player.object.preferred_source === 'YouTube') {

                // this.debugMessage(player.player)
                try {
                    const state = await player.player.getPlayerState();
                    // this.debugMessage('YouTube player state: ' + state);
                    playing = state === 1;

                    // while we are here lets get the current time position
                    this.currentTimePosition = await player.player.getCurrentTime();

                } catch (error) {
                    throw new Error(error);
                }

            }

            else if (player.object.preferred_source === 'Vimeo') {

                try {
                    let paused;
                    // paused = toRaw(player.player).getPaused().then((value) => {
                    //     playing = !value;
                    // });
                    // this.debugMessage('Vimeo player state: ' + playing);
                    paused = await toRaw(player.player).getPaused();
                    // this.debugMessage('Vimeo player state: ' + paused);
                    playing = !paused;

                    // while we are here lets get the current time position
                    this.currentTimePosition = await toRaw(player.player).getCurrentTime();

                } catch (error) {
                    throw new Error(error);
                }

            }

            else if (player.object.preferred_source === 'Dailymotion') {
                try {
                    const state = await player.player.getState();
                    console.log(state);

                    playing = state.playerIsPlaying;
                    console.log(playing);

                    // while we are here lets get the current time position
                    this.currentTimePosition = state.videoTime;

                    this.debugMessage('Dailymotion player state: ' + playing);


                } catch (error) {
                    throw new Error(error);
                }
            } else if (player.object.preferred_source === 'Twitch') {
                // let player = await toRaw(player.player);
                //
                // let paused = await toRaw(player.player).isPaused();
                // playing = !paused;
                // this.debugMessage('Twitch player state: ' + playing);
                //
                // // while we are here lets get the current time position
                // this.currentTimePosition = await toRaw(player.player).getCurrentTime();
            }

            return playing;
        },

        pauseViewRecord(external_id = null) {
            this.debugMessage('pause view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }

        },

        stopViewRecord(external_id = null) {
            this.debugMessage('stop view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
            this.viewRecordDuration = 0;
        },

        destroyPlayers() {
            // iterate through players and get object external_id and destroy div using that as id
            this.players.forEach(item => {
                this.destroyItem(item).then(r => {})
            });

            this.stopViewRecord();
            this.players = [];
        },

        async destroyItem(item) {
            // check if player exists
            if (!item) {
                return;
            }

            if (item.object.preferred_source === 'YouTube') {
                item.player.destroy();
            } else if (item.object.preferred_source === 'Vimeo') {
                await toRaw(item.player).unload();
            } else if (item.object.preferred_source === 'Dailymotion') {
                await item.player.destroy();
            } else if (item.object.preferred_source === 'Twitch') {
                await toRaw(item.player).destroy();
            }

            let playerDiv = document.getElementById(item.object.external_id);
            if (playerDiv) {
                playerDiv.remove();
            }
            // remove player from players array
            this.players = this.players.filter(player => player.object.external_id !== item.object.external_id);

        },


        async buildPlayer(playerDivHolderID = null, object, startTime = 0, autoplay = false, checkViewHistoryStartTime = true) {

            // run this.loadScripts() but wait for it to finish the scripts to actually load

            // until scriptsLoaded is true wait 1 second and try again
            if (!this.scriptsLoaded) {
                await this.loadScripts(); // don't worry about this running multiple times, it checks if the script by id exists before trying to add it again
                setTimeout(() => {
                    this.debugMessage('scripts not loaded yet, trying again in 1 second')
                    this.buildPlayer(playerDivHolderID, object, startTime, autoplay, checkViewHistoryStartTime);
                }, 1000);
                return;
            }

            if (checkViewHistoryStartTime && usePage().props.auth.user !== null) {
                this.debugMessage('checking view history start time')
                // get the view history for this video and set the start time to the last time they watched it
                const videoId = object.id;
                try {
                    const response = await axios.get(route('video.interaction', {videoId: videoId}));
                    const data = response.data;
                    if (data !== undefined && data.view_point !== null) {
                        this.debugMessage('setting start time to: ' + data.view_point)
                        startTime = data.view_point;
                    }
                } catch (error) {
                    console.log(error);
                }

            }


            //create player_div element inside miniplayer_div_holder
            if (playerDivHolderID === null) {
                playerDivHolderID = document.getElementById('miniplayer_div_holder');
            } else {
                playerDivHolderID = document.getElementById(playerDivHolderID);
            }

            if(playerDivHolderID === null){
                console.log('CATASTROPHIC ERROR!!!! a playerDivHolder could be not found for this player with id: ' + playerDivHolderID);
                console.log(playerDivHolderID)
                console.log(object)
                return;
            }

            //remove all children of player_div_holder
            while (playerDivHolderID.firstChild) {
                playerDivHolderID.removeChild(playerDivHolderID.firstChild);
            }

            this.show = true;

            this.debugMessage('build player ' + object.preferred_source);

            // embeds often rebuild the div they are in which is a pain so we will just remove the div and rebuild it


            // if div with id of object.external_id exists remove it
            if (document.getElementById(object.external_id)) {
                document.getElementById(object.external_id).remove();
            }

            console.log(object.external_id)

            // // create player_div element inside player_div_holder
            let playerDiv = playerDivHolderID.appendChild(document.createElement('div'));
            playerDiv.id = object.external_id;

            // // add h-full and w-full classes to player_div
            playerDiv.classList.add('h-full');
            playerDiv.classList.add('w-full');

            // // create player
            if (object.preferred_source === "YouTube") {
                this.buildYouTubePlayer(playerDiv, object, startTime, autoplay);
                playerDiv.removeAttribute('style');
            } else if (object.preferred_source === "Vimeo") {
                this.buildVimeoPlayer(playerDiv, object, startTime, autoplay);
                playerDiv.removeAttribute('style');
            } else if (object.preferred_source === "Dailymotion") {
                this.buildDailymotionPlayer(playerDiv, object, startTime, autoplay);
                playerDiv.removeAttribute('style');
            } else if (object.preferred_source === "Twitch") {
                this.buildTwitchPlayer(playerDiv, object, startTime, autoplay);
            }
        },

        buildYouTubePlayer(playerDiv, object, startTime = 0, autoplay = false) {
            let external_id = object.external_id;

            const player = new window.YT.Player(playerDiv, {
                videoId: external_id,
                playerVars: {
                    'autoplay': autoplay ? 1 : 0,
                    'controls': 1,
                    'modestbranding': 1,
                    'rel': 0,
                    'showinfo': 0,
                    'start': startTime,
                },
                events: {
                    // when YouTube video ends run the endVideo function
                    onStateChange: (event) => {
                        if (event.data === 0) { // this state means the video has ended
                            // this.debugMessage('BUILDYouTube: YouTube video ended')
                            this.endVideo(external_id);
                        }
                        if (event.data === 1) { // this state means the video is playing
                            // this.debugMessage('BUILDYouTube: YouTube video playing')
                            this.startViewRecord(external_id);
                        }
                        if (event.data === 2) { // this state means the video is paused
                            // this.debugMessage('BUILDYouTube: YouTube video paused')
                            this.pauseViewRecord(external_id);
                        }
                        if (event.data === 3) { // this state means the video is buffering
                            // this.debugMessage('BUILDYouTube: YouTube video buffering')
                            this.pauseViewRecord(external_id);
                        }
                    }
                }
            });

            this.pushPlayer(player, object);
        },

        buildVimeoPlayer(playerDiv, object, startTime = 0, autoplay = false) {
            let external_id = object.external_id;
            let htmlcollection;

            const player = new Vimeo.Player(playerDiv.id, {
                id: object.external_id,
                responsive: true,
                autopause: !autoplay
            });

            player.on('loaded', () => {
                // wait for player to load then set start time
                player.ready().then(function () {

                    // set up
                    if (autoplay) {
                        player.play();
                    }
                    player.setCurrentTime(startTime);

                    //styling

                        // this.debugMessage(document.getElementById(playerDiv.id).firstElementChild);
                    document.getElementById(playerDiv.id).firstElementChild.classList.add("h-full", "w-full","p-0", "relative");
                    document.getElementById(playerDiv.id).firstElementChild.removeAttribute("style");

                    // reset all Vimeo players to default size
                    htmlcollection = document.getElementsByClassName("player");
                    for (let i = 0; i < htmlcollection.length; i++) {
                        htmlcollection[i].removeAttribute("style");
                    }
                }.bind(this));
            });

            player.on('play', () => {
                this.startViewRecord(external_id);
            });

            player.on('pause', () => {
                this.pauseViewRecord(external_id);
            });

            player.on('ended', () => {
                this.endVideo(external_id);

            });

            player.ready().then(function () {
                // this.debugMessage('BUILDVimeo: Vimeo player ready');
                // ensure player is ready before pushing to players array
                this.pushPlayer(player, object);

            }.bind(this));


        },

        buildDailymotionPlayer(playerDiv, object, startTime = 0, autoplay = false) {
            const external_id = object.external_id;
            console.log(playerDiv.id)
            let player;


            dailymotion.createPlayer(playerDiv.id, {
                video: external_id,
                params: {
                    startTime: startTime,
                    autoplay: autoplay,
                    mute: false,

                }
            }).then((resolvedPlayer) => {
                player = resolvedPlayer;

                // don't remove these 2 lines, they are need otherwise the player disappears into the ether
                document.getElementById(playerDiv.id).classList.add("h-full", "w-full", "p-0", "relative");
                document.getElementById(playerDiv.id).removeAttribute('style');

                player.on(dailymotion.events.PLAYER_VIDEOCHANGE, () => {
                    // this.debugMessage('BUILDDailymotion: Dailymotion player ready')
                    if (autoplay) {
                        player.play();
                    }
                });

                player.on(dailymotion.events.VIDEO_PLAY, () => {
                    this.startViewRecord(external_id);
                });

                player.on(dailymotion.events.VIDEO_PAUSE, () => {
                    this.pauseViewRecord(external_id);
                });

                player.on(dailymotion.events.VIDEO_END, () => {
                    this.endVideo(external_id);
                });

                this.pushPlayer(player, object);
            });
        },

        buildTwitchPlayer(playerDiv, object, startTime = 0, autoplay = false) {
            // let external_id = object.external_id;
            let external_id = 'monstercat';
            const player = new Twitch.Player(playerDiv, {
                channel: external_id,
                parent: ["localhost","127.0.0.1","vidgaze.tv","www.vidgaze.tv","www.staging.vidgaze.tv","staging.vidgaze.tv"],
                width: '100%',
                height: '100%',
                autoplay: autoplay ? 1 : 0,
                controls: true,
            });

            // on play start view record
            player.addEventListener(Twitch.Player.PLAY, () => {
                this.startViewRecord(external_id);
            });

            // on pause stop view record
            player.addEventListener(Twitch.Player.PAUSE, () => {
                this.pauseViewRecord(external_id);
            });

            // on video end stop view record
            player.addEventListener(Twitch.Player.ENDED, () => {
                this.endVideo(external_id);
            });

            this.pushPlayer(player, object);
        },

        pushPlayer(player, object) {
            // check if player is already in players array
            if (this.findPlayer(object.external_id)) {
                this.debugMessage('player already in array')
                return;
            }

            if (!object) {
                this.debugMessage('MASSIVE BUG HERE !!!!!!!!! PUSHPLAYER: object not found')
            }

            //create player and add to players array and then reset variables
            this.players.push(
                {
                    'object': object,
                    'player': player
                }
            )


        },

        findPlayer(external_id) {
            // find player in players array
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].external_id === external_id) {
                    this.debugMessage('FINDPLAYER: found player' + external_id);
                    return this.players[i];
                }
            }
            this.debugMessage('FINDPLAYER: player not found' + external_id);
            return false;
        },


        async play(external_id) {
            this.debugMessage('PLAY: ' + external_id);
            const player = this.findPlayer(external_id);
            // if player is not found, return
            if (!player) {
                this.debugMessage('PLAY: player not found');
                return;
            }
            // console.log(player);

            // pause all other players
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].external_id !== external_id) {
                    await this.pause(this.players[i]['object'].external_id);
                }
            }
            console.log([player.object.preferred_source, player]);

            // check object preferred source
            if (["Vimeo", "Twitch"].includes(player.object.preferred_source)) {
                await toRaw(player.player).play();
            } else if (player.object.preferred_source === "Dailymotion") {
                await player.player.play();
            } else if (player.object.preferred_source === "YouTube") {
                // console.log(player.player);
                await player.player.playVideo();
            }

        },

        async pause(external_id) {
            const player = this.findPlayer(external_id);
            // if player is not found, return
            if (!player && await this.isPlayerPlaying(player)) {
                return;
            }
            console.log([player.object.preferred_source, player]);

            // check object preferred source
            if (["Vimeo", "Twitch"].includes(player.object.preferred_source)) {
                await toRaw(player.player).pause();
            } else if (player.object.preferred_source === "Dailymotion") {
                await player.player.pause();
            } else if (player.object.preferred_source === "YouTube") {
                await player.player.pauseVideo();
            }
        },



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
            this.debugMessage('load scripts');
            // load scripts
            await this.loadScript('https://geo.dailymotion.com/libs/player/xfjc3.js', 'dailymotion-api')
            await this.loadScript('https://www.youtube.com/iframe_api', 'youtube-api');
            await this.loadScript('https://player.vimeo.com/api/player.js', 'vimeo-api');
            await this.loadScript('https://player.twitch.tv/js/embed/v1.js', 'twitch-api');

            this.scriptsLoaded = true

        }
    }
})
