import {defineStore} from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";
import {toRaw} from "vue";
import {usePage} from "@inertiajs/vue3";

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            debug: true,
            object: null,
            autoplay: false,
            start_time: 0,
            players: [], //in the form of [[type => "video", object => {id: 1, ...}, player => player_object], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            isViewRecording: false,
            viewRecordTimer: null,
            viewRecordDuration: 0,
        }
    },
    getters: {
        showMiniPlayer() {
            // Compute the value of showMiniPlayer based on your logic
            // For example, you can check if players array is not empty
            let queueStore = useQueueStore();
            // also depends on what page you are on ...
            return queueStore.items !== undefined && queueStore.items.length > 0 && usePage().url !== '/shorts';
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
                    this.destroyPlayers();
                    this.showMiniPlayer = false;
                }
            }
            this.debugMessage('end video' + external_id);
        },

        startViewRecord(external_id) {
            this.debugMessage('start view record');
            if (!this.isViewRecording) {
                this.isViewRecording = true;
                let player = this.findPlayer(external_id);

                this.viewRecordTimer = setInterval(async () => {
                    try {
                        const isPlaying = await this.isPlayerPlaying(player);
                        if (isPlaying) {
                            this.viewRecordDuration += 5;
                            this.debugMessage('STARTVIEWRECORD: View Record Duration: ' + this.viewRecordDuration);
                        } else {
                            // this.debugMessage('STARTVIEWRECORD: Error: Player is not playing');
                            clearInterval(this.viewRecordTimer);

                        }
                    } catch (error) {
                        this.debugMessage('STARTVIEWRECORD: Error: ' + error);
                        clearInterval(this.viewRecordTimer);
                    }
                }, 5000);
            }
        },


        // has to be async as we need to wait for the dailymotion player to be ready
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
                    // this.debugMessage('Youtube player state: ' + state);
                    playing = state === 1;
                } catch (error) {
                    throw new Error(error);
                }

            } else if (player.object.preferred_source === 'Vimeo') {

                try {
                    let paused;
                    // paused = toRaw(player.player).getPaused().then((value) => {
                    //     playing = !value;
                    // });
                    // this.debugMessage('Vimeo player state: ' + playing);
                    paused = await toRaw(player.player).getPaused();
                    // this.debugMessage('Vimeo player state: ' + paused);
                    playing = !paused;
                } catch (error) {
                    throw new Error(error);
                }

            } else if (player.object.preferred_source === 'Dailymotion') {
                try {
                    const state = await player.player.getState();
                    playing = state.playerIsPlaying;
                    // this.debugMessage('Dailymotion player state: ' + playerIsPlaying);
                } catch (error) {
                    throw new Error(error);
                }
            } else if (player.object.preferred_source === 'Twitch') {
                let paused = await toRaw(player.player).isPaused();
                playing = !paused;
                this.debugMessage('Twitch player state: ' + playing);
            }

            return playing;
        },

        pauseViewRecord(external_id) {
            this.debugMessage('pause view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }

        },

        stopViewRecord(external_id) {
            this.debugMessage('stop view record' + external_id);
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
            this.viewRecordDuration = 0;
        },

        destroyPlayers() {
            // document.querySelectorAll('#player_div').forEach(n => n.remove());

            // iterate through players and get object external_id and destroy div using that as id
            this.players.forEach(player => {
                let playerDiv = document.getElementById(player.object.external_id);
                playerDiv.remove();
            });


            this.stopViewRecord();
            this.players = [];
        },

        destroyPlayer(external_id) {
            // iterate through players and get object external_id and destroy div using that as id
            this.players.forEach(player => {
                if (player.object.external_id === external_id) {
                    let playerDiv = document.getElementById(player.object.external_id);
                    playerDiv.remove();
                    // remove player from players array
                    this.players = this.players.filter(player => player.object.external_id !== external_id);
                }
            });
        },

        resetVariables() {
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;
            this.type = null;
        },

        buildPlayer(playerDivHolderID = null) {

            //create player_div element inside player_div_holder
            if (!playerDivHolderID) {
                playerDivHolderID = document.getElementById('player_div_holder');
            } else {
                playerDivHolderID = document.getElementById(playerDivHolderID);
            }

            this.show = true;
            // embeds often rebuild the div they are in which is a pain so we will just remove the div and rebuild it
            // // create player_div element inside player_div_holder
            let playerDiv = playerDivHolderID.appendChild(document.createElement('div'));
            playerDiv.id = this.object.external_id;
            // // add h-full and w-full classes to player_div
            playerDiv.classList.add('h-full');
            playerDiv.classList.add('w-full');

            // // create player
            if (this.object.preferred_source === "YouTube") {
                this.buildYouTubePlayer(playerDiv);
            } else if (this.object.preferred_source === "Vimeo") {
                this.buildVimeoPlayer(playerDiv);
            } else if (this.object.preferred_source === "Dailymotion") {
                this.buildDailymotionPlayer(playerDiv);
            } else if (this.object.preferred_source === "Twitch") {
                this.buildTwitchPlayer(playerDiv);
            }



        },

        buildYouTubePlayer(playerDiv) {
            let external_id = this.object.external_id;

            const player = new window.YT.Player(playerDiv, {
                videoId: this.object.external_id,
                // videoId: 'dQw4w9WgXcQ',
                playerVars: {
                    'autoplay': this.autoplay ? 1 : 0,
                    'controls': 1,
                    'modestbranding': 1,
                    'rel': 0,
                    'showinfo': 0,
                    'start': this.start_time,
                },
                events: {
                    // when YouTube video ends run the endVideo function
                    onStateChange: (event) => {
                        if (event.data === 0) { // this state means the video has ended
                            this.debugMessage('BUILDYOUTUBE: YouTube video ended')
                            this.endVideo(external_id);
                        }
                        if (event.data === 1) { // this state means the video is playing
                            this.debugMessage('BUILDYOUTUBE: YouTube video playing')
                            this.startViewRecord(external_id);
                        }
                        if (event.data === 2) { // this state means the video is paused
                            this.debugMessage('BUILDYOUTUBE: YouTube video paused')
                            this.pauseViewRecord(external_id);
                        }
                        if (event.data === 3) { // this state means the video is buffering
                            this.debugMessage('BUILDYOUTUBE: YouTube video buffering')
                            this.pauseViewRecord(external_id);
                        }
                    }
                }
            });

            this.pushPlayer(player);
        },

        buildVimeoPlayer(playerDiv) {
            let external_id = this.object.external_id;

            // external_id = '822998068';
            // this.object.external_id = external_id;


            const player = new Vimeo.Player(playerDiv.id, {
                id: this.object.external_id,
                autoplay: this.autoplay ? 1 : 0,
                responsive: true,
                autopause: true,
            });

            player.on('loaded', () => {
                // wait for player to load then set start time
                player.ready().then(function () {
                        player.setCurrentTime(this.start_time);
                        // this.debugMessage(document.getElementById(playerDiv.id).firstElementChild);
                        document.getElementById(playerDiv.id).firstElementChild.classList.add("h-full", "w-full","p-0", "relative");
                        document.getElementById(playerDiv.id).firstElementChild.removeAttribute('style');
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
                // this.debugMessage('BUILDVIMEO: Vimeo player ready');
                // ensure player is ready before pushing to players array
                this.pushPlayer(player);

            }.bind(this));


        },

        buildDailymotionPlayer(playerDiv) {
            // const external_id = this.object.external_id;
            const external_id = 'x8l6g4x';
            this.object.external_id = external_id;

            const tag = document.createElement('script' );
            tag.src = "https://geo.dailymotion.com/libs/player/" + external_id + ".js";
            tag.id = 'tag' + external_id;
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            let player;
            //wait for script to load
            setTimeout(() => {
                dailymotion.createPlayer(playerDiv.id, {
                    video: external_id,
                    start: this.start_time,
                    autoplay: this.autoplay,
                }).then((resolvedPlayer) => {
                    player = resolvedPlayer;
                    document.getElementById(playerDiv.id).classList.add("h-full", "w-full","p-0", "relative");
                    document.getElementById(playerDiv.id).removeAttribute('style');
                    // console.log(player);

                    player.on(dailymotion.events.VIDEO_PLAY, () => {
                        this.startViewRecord(external_id);
                    });

                    player.on(dailymotion.events.VIDEO_PAUSE, () => {
                        this.pauseViewRecord(external_id);
                    });

                    player.on(dailymotion.events.VIDEO_END, () => {
                        this.endVideo(external_id);
                    });

                    this.pushPlayer(player);
                });
            }, 1000);


        },

        buildTwitchPlayer(playerDiv) {
            let external_id = this.object.external_id;
            const player = new Twitch.Player(playerDiv, {
                // video: this.object.id,
                channel: 'monstercat',
                parent: ["localhost","127.0.0.1","vidgaze.tv","www.vidgaze.tv","www.staging.vidgaze.tv","staging.vidgaze.tv"],
                width: '100%',
                height: '100%',
                autoplay: this.autoplay ? 1 : 0,
                controls: true,
            });

            this.pushPlayer(player);
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

        },

        pushPlayer(player) {
            // check if player is already in players array
            if (this.findPlayer(this.object.external_id)) {
                this.debugMessage('player already in array')
                return;
            }
            if (this.object === null) {
                this.debugMessage('object is null')
                return;
            }

            //create player and add to players array
            this.players.push(
                {
                    'object': this.object,
                    'player': player
                }
            );

            // sometimes reset variables runs before the player is ready so we will wait 5 seconds
            setTimeout(() => {
                this.resetVariables();
            }, 5000);
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

        play(external_id) {
            const player = this.findPlayer(external_id);
            // if player is not found, return
            if (!player) {
                return;
            }

            // pause all other players
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].external_id !== external_id) {
                    this.pause(this.players[i]['object'].id, this.players[i]['type']);
                }
            }

            // check object preferred source
            if (["Vimeo", "Dailymotion", "Twitch"].includes(this.object.preferred_source)) {
                player.player.play();
            } else if (this.object.preferred_source === "YouTube") {
                player.player.playVideo();
            }

        },

        pause(external_id) {
            const player = this.findPlayer(external_id);
            // if player is not found, return
            if (!player) {
                return;
            }

            // check object preferred source
            if (["Vimeo", "Dailymotion", "Twitch"].includes(this.object.preferred_source)) {
                player.player.pause();
            } else if (this.object.preferred_source === "YouTube") {
                player.player.pauseVideo();
            }
        },
    }
})
