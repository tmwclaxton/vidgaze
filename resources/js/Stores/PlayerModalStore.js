import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            object: null,
            type: null,
            players: [], //in the form of [[object => {id: 1, ...}, player => {}], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            show: false,
            autoplay: true,
            start_time: 47,
        }
    },
    actions: {

        play(player_id) {
            const player = this.players.find(({ object }) => object.id === player_id);

            // if player is not found, return
            if (!player) {
                return;
            }

            // check object preferred source
            if (this.object.preferred_source === "YouTube") {
                player.player.playVideo();
            }
        },


        buildPlayer() {
            this.show = true;



            // embeds often rebuild the div they are in which is a pain so we will just remove the div and rebuild it

            //create player_div element inside player_div_holder
            let playerDivHolder = document.getElementById('player_div_holder');
            // // create player_div element inside player_div_holder
            let playerDiv = playerDivHolder.appendChild(document.createElement('div'));
            playerDiv.id = 'player_div';
            // // add h-full and w-full classes to player_div
            playerDiv.classList.add('h-full');
            playerDiv.classList.add('w-full');

            // // create player
            if (this.object.preferred_source === "YouTube") {
                this.buildYouTubePlayer(playerDiv);
            }
            else if (this.object.preferred_source === "Vimeo") {
                this.buildVimeoPlayer(playerDiv);
            } else if (this.object.preferred_source === "Dailymotion") {
                this.buildDailymotionPlayer(playerDiv);
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
                    // onReady: onPlayerReady,
                    // onStateChange: onPlayerStateChange,
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
            })

            this.pushPlayer(player);
        },

        buildDailymotionPlayer(playerDiv) {
            // const videoId = this.object.id;
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

                        // player.on(dailymotion.events.VIDEO_START, () => {
                        //     player.seek(startTime);
                        // });


                    });
                this.pushPlayer(player);
            } , 500);

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
            this.players.forEach(({ player }) => {
                player.destroy();
            });

            // if playerDivHolder exists, delete it
            let playerDivHolder = document.getElementById('player_div_holder');
            //delete divs
            if (playerDivHolder) {
                // delete all children of playerDivHolder
                playerDivHolder.querySelectorAll('*').forEach(n => n.remove());
            }
            this.players = [];
        },

        resetVariables() {
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;
        }



    }
})
