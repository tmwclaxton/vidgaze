import { defineStore } from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            debug: true,
            object: null,
            type: null,
            autoplay: false,
            start_time: 0,
            players: [], //in the form of [[type => "video", object => {id: 1, ...}, player => player_object], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
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

        findPlayer(player_id, player_type) {
            // find player in players array
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].id === player_id && this.players[i]['type'] === player_type) {
                    return this.players[i];
                }
            }
            return false;
        },

        play(player_id, player_type) {
            const player = this.findPlayer(player_id, player_type);
            // if player is not found, return
            if (!player) {
                return;
            }

            // pause all other players
            for (let i = 0; i < this.players.length; i++) {
                if (this.players[i]['object'].id !== player_id || this.players[i]['type'] !== player_type) {
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

        pause(player_id, player_type) {
            const player = this.findPlayer(player_id, player_type);
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

        endVideo() {
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

        startViewRecord() {
            // this.debugMessage('start view record');
            if (!this.isViewRecording) {
                this.isViewRecording = true;
                this.viewRecordTimer = setInterval(() => {
                    this.viewRecordDuration += 5;
                    this.debugMessage('View Record Duration: ' + this.viewRecordDuration);
                }, 5000);
            }
        },

        pauseViewRecord() {
            this.debugMessage('pause view record');
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }

        },

        stopViewRecord() {
            this.debugMessage('stop view record');
            if (this.isViewRecording) {
                this.isViewRecording = false;
                clearInterval(this.viewRecordTimer);
            }
            this.viewRecordDuration = 0;
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


            // sometimes reset variables runs before the player is ready so we will wait 5 seconds
            setTimeout(() => {
                this.resetVariables();
            }, 5000);
        },

        buildYouTubePlayer(playerDiv) {
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
                    onReady: this.startViewRecord(),
                    // when YouTube video ends run the endVideo function
                    onStateChange: (event) => {
                        if (event.data === 0) {
                            this.endVideo();
                        }
                        if (event.data === 1) {
                            this.startViewRecord();
                        }
                        if (event.data === 2) {
                            this.pauseViewRecord();
                        }
                    }
                }
            });

            this.pushPlayer(player);
        },

        buildVimeoPlayer(playerDiv) {

            const player = new Vimeo.Player(playerDiv, {
                id: this.object.external_id,
                // id: '822998068',
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
                this.startViewRecord();
            });

            player.on('pause', () => {
                this.pauseViewRecord();
            });

            player.on('ended', () => {
                this.endVideo();
            });

            this.pushPlayer(player);
        },

        buildDailymotionPlayer(playerDiv) {
            const videoId = this.object.external_id;
            // const videoId = 'x8l6g4x';
            if (!document.getElementById(videoId)) {
                const tag = document.createElement('script');
                tag.src = "https://geo.dailymotion.com/libs/player/" + videoId + ".js";
                tag.id = videoId;
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }

            //wait for script to load
            setTimeout(() => {
                const player =
                dailymotion.createPlayer(playerDiv.id, {
                    video: videoId,
                    start: this.start_time,
                })
                    .then((player) => {
                        this.pushPlayer(player);

                        player.on(dailymotion.events.VIDEO_PLAY, () => {
                            this.startViewRecord();
                        });

                        player.on(dailymotion.events.VIDEO_PAUSE, () => {
                            this.pauseViewRecord();
                        });

                        player.on(dailymotion.events.VIDEO_END, () => {
                            this.endVideo();
                        });


                    });
                this.pushPlayer(player);
            } , 1000);

        },

        buildTwitchPlayer(playerDiv) {
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
                    this.startViewRecord();
                });

                // on pause stop view record
                player.addEventListener(Twitch.Player.PAUSE, () => {
                    this.pauseViewRecord();
                });

                // on video end stop view record
                player.addEventListener(Twitch.Player.ENDED, () => {
                    this.endVideo();
                });

        },

        pushPlayer(player) {
            //create player and add to players array
            this.players.push(
                {
                    type: this.type,
                    object: this.object,
                    player: player
                }
            );
        },

        destroyPlayers() {
            document.querySelectorAll('#player_div').forEach(n => n.remove());
            this.stopViewRecord();
            this.players = [];
        },

        destroyPlayer(player_id, player_type) {
            const player = this.findPlayer(player_id, player_type);
            // if player is not found, return
            if (!player) {
                return;
            }
            // player.player.destroy(); // this won't work for twitch
            this.players = this.players.filter((item) => item.player !== player.player);
        },

        resetVariables() {
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;
            this.type = null;
        }

    }
})
