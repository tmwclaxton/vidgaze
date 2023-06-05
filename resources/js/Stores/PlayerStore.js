import { defineStore } from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            debug: true,
            object: null,
            autoplay: false,
            start_time: 0,
            players: [], //in the form of [[type => "video", object => {id: 1, ...}, player => player_object], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            current_player_index: 0,
            showMiniPlayer: false,
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
            return queueStore.items !== undefined && queueStore.items.length > 0;
        },
    },

    actions: {
        debugMessage(message) {
            if (this.debug) {
                console.log(message);
            }
        },


        endVideo(external_id) {
            // if shorts scroll to next short
            // if not short check if queue has items and play next item
            // if not shorts and miniplayer with no queue items, stop player and close player modal
            // if not shorts and no miniplayer, stop player and show end screen with suggestions
            this.stopViewRecord();

            if (this.showMiniPlayer) {
                // check if queue has an item after this one
                let queueStore = useQueueStore();

                // wait 1 - as if we are deleting the item from the queue it will take a second to update
                // setTimeout(() => {
                    if (queueStore.items.length > queueStore.index + 1) {
                        queueStore.changeIndex(queueStore.index + 1);
                        this.debugMessage('mini player next item');
                    } else {
                        this.destroyPlayers();
                        this.showMiniPlayer = false;
                    }
                // }, 1000);

            }

            this.debugMessage('end video');
        },

        startViewRecord(external_id) {
            this.debugMessage('start view record');
            if (!this.isViewRecording) {
                this.isViewRecording = true;

                //test
                let player = this.findPlayer(external_id);
                // player.player.getPaused().then((paused) => { !this.debugMessage(paused) }) ; // doesn't work at this stage

                this.viewRecordTimer = setInterval(async () => {
                    try {
                        const isPlaying = await this.isPlayerPlaying(external_id);
                        if (isPlaying) {
                            this.viewRecordDuration += 5;
                            this.debugMessage('View Record Duration: ' + this.viewRecordDuration);
                        } else {
                            this.debugMessage('Error: Player is not playing');
                        }
                    } catch (error) {
                        this.debugMessage('Error: ' + error);
                    }
                }, 5000);
            }
        },


        // has to be async as we need to wait for the dailymotion player to be ready
        async isPlayerPlaying(external_id) {
            this.debugMessage(external_id + ' is player playing');
            let player = this.findPlayer(external_id);

            if (!player) {
                this.debugMessage('Player not found');
                throw new Error('Player not found');
            }

            let playing = false;

            if (player.object.preferred_source === 'YouTube') {

                this.debugMessage(player.player)
                try {
                    const state = await player.player.getPlayerState();
                    this.debugMessage('Youtube player state: ' + state);
                    playing = state === 1;
                } catch (error) {
                    throw new Error(error);
                }

            } else if (player.object.preferred_source === 'Vimeo') {

                try {
                    // access the target inside the event
                    this.debugMessage(player.player)
                    player.player.getPaused().then((value) => {
                        console.log(value);
                        playing = !value;
                    });
                    // const paused = await player.player.getPaused();
                    // this.debugMessage('Vimeo player state: ' + paused);
                    // playing = !paused;
                } catch (error) {
                    throw new Error(error);
                }

            } else if (player.object.preferred_source === 'Dailymotion') {
                try {
                    const state = await player.player.getState();
                    const playerIsPlaying = state.playerIsPlaying;
                    this.debugMessage('Dailymotion player state: ' + playerIsPlaying);
                    playing = playerIsPlaying;
                } catch (error) {
                    throw new Error(error);
                }
            } else if (player.object.preferred_source === 'Twitch') {
                playing = !player.player.isPaused();
            }

            return playing;
        },

        pauseViewRecord(external_id) {
            this.debugMessage('pause view record');
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }

        },

        stopViewRecord(external_id) {
            this.debugMessage('stop view record');
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
            this.viewRecordDuration = 0;
        },

        destroyPlayers() {
            document.querySelectorAll('#player_div').forEach(n => n.remove());
            this.stopViewRecord();
            this.players = [];
        },

        resetVariables() {
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;
            this.type = null;
        },

        buildPlayer(playerDivHolder = null) {

            //create player_div element inside player_div_holder
            if (!playerDivHolder) {
                playerDivHolder = document.getElementById('player_div_holder');
            }

            this.show = true;
            // embeds often rebuild the div they are in which is a pain so we will just remove the div and rebuild it


            // // create player_div element inside player_div_holder
            let playerDiv = playerDivHolder.appendChild(document.createElement('div'));
            playerDiv.id = 'player_div';
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
                    onReady: this.startViewRecord(external_id),
                    // when YouTube video ends run the endVideo function
                    onStateChange: (event) => {
                        if (event.data === 0) {
                            this.endVideo(external_id);
                        }
                        if (event.data === 1) {
                            this.startViewRecord(external_id);
                        }
                        if (event.data === 2) {
                            this.pauseViewRecord(external_id);
                        }
                    }
                }
            });

            this.pushPlayer(player);
        },

        buildVimeoPlayer(playerDiv) {
            let external_id = this.object.external_id;

            external_id = '822998068';
            this.object.external_id = external_id;


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
                this.debugMessage('Vimeo player ready');
                // ensure player is ready before pushing to players array
                this.pushPlayer(player);

            }.bind(this));


        },

        buildDailymotionPlayer(playerDiv) {
            const external_id = this.object.external_id;
            // const external_id = 'x8l6g4x';
            // this.object.external_id = external_id;
            if (!document.getElementById(external_id)) {
                const tag = document.createElement('script');
                tag.src = "https://geo.dailymotion.com/libs/player/" + external_id + ".js";
                tag.id = external_id;
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }
            let player;
            //wait for script to load
            setTimeout(() => {
                dailymotion.createPlayer(playerDiv.id, {
                    video: external_id,
                    start: this.start_time,
                }).then((resolvedPlayer) => {
                    player = resolvedPlayer;

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

            // player.getPaused().then((paused) => { !this.debugMessage(paused) }) ; works here for vimeo
            this.debugMessage('pushing player' + this.object.external_id)
            console.log(player);
            // check if player is already in players array
            if (this.findPlayer(this.object.external_id)) {
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
                    this.debugMessage('found player' + external_id);
                    return this.players[i];
                }
            }
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
