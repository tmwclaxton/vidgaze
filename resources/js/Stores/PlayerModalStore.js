import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            object: null,
            type: null,
            players: [], //in the form of [[object => {id: 1, ...}, player => {}], ...] this is so we can have multiple players on the page like for shorts and be able to control them individually
            show: false,
            autoplay: false,
            start_time: 0,
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
            console.log('building player');
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
            //
            // // create player
            if (this.object.preferred_source === "YouTube") {
                this.buildYouTubePlayer(playerDiv);
            }
            //
            // //reset variables
            this.autoplay = false;
            this.start_time = 0;
            this.object = null;

        },

        buildYouTubePlayer(playerDiv) {
            //create player and add to players array
            this.players.push(
                {
                    object: this.object,
                    player:
                        new window.YT.Player(playerDiv, {
                            videoId: this.object.id,
                            playerVars: {
                                autoplay: this.autoplay ? 1 : 0,
                                controls: 1,
                                modestbranding: 1,
                                rel: 0,
                                showinfo: 0,
                                start: this.start_time,
                            },
                            events: {
                                // onReady: onPlayerReady,
                                // onStateChange: onPlayerStateChange,
                            }
                        })
                }
            );
        },

        //destroy players
        destroyPlayers() {
            console.log('destroying players');
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
        }



    }
})
