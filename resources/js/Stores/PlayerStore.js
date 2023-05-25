import { defineStore } from 'pinia'
import {useQueueStore} from "@/Stores/QueueStore";

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            object: null,
            type: null,
            autoplay: false,
            start_time: 0,
            players: [], //in the form of [[object => {id: 1, ...}, player => {}], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            showMiniPlayer: false,
        }
    },
    getters: {
        showMiniPlayer: (state) => {
            // Compute the value of showMiniPlayer based on your logic
            // For example, you can check if players array is not empty
            let queueStore = useQueueStore();
            return queueStore.items !== undefined && queueStore.items.length > 0;
        },
    },

    actions: {

        play(player_id) {
            const player = this.players.find(({ object }) => object.id === player_id);
            // if player is not found, return
            if (!player) {
                return;
            }

            // check object preferred source
            if (["Vimeo", "Dailymotion", "Twitch"].includes(this.object.preferred_source)) {
                player.player.play();
            } else if (this.object.preferred_source === "YouTube") {
                player.player.playVideo();
            }

        },

        pause(player_id) {
            const player = this.players.find(({ object }) => object.id === player_id);
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
          // if not shorts, check if queue has items and play next item
            // if not shorts and no queue items, stop player and close player modal
            console.log('end video');
        },

        startViewRecord() {
            console.log('start view record');
        },

        pauseViewRecord() {
            console.log('pause view record');

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
                // videoId: this.object.id,
                videoId: 'dQw4w9WgXcQ',
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
                // id: this.object.id,
                id: '822998068',
                autoplay: this.autoplay ? 1 : 0,
                responsive: true,
                autopause: true,
            });

            player.on('loaded', () => {
                // wait for player to load then set start time
                player.ready().then(function () {
                    player.setCurrentTime(this.start_time);
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
            // const videoId = this.object.external_id;
            const videoId = 'x8l6g4x';
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
            } , 500);

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
                    object: this.object,
                    player: player
                }
            );
        },

        destroyPlayers() {
            document.querySelectorAll('#player_div').forEach(n => n.remove());
            this.players = [];
        },

        destroyPlayer(player_id) {
            const player = this.players.find(({ object }) => object.id === player_id);
            // if player is not found, return
            if (!player) {
                return;
            }
            // player.player.destroy(); // this won't work for twitch
            this.players = this.players.filter(({ object }) => object.id !== player_id);
        },

        resetVariables() {
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;
        }

    }
})
