import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            object: {id: "M7lc1UVf-VE",preferred_source: "YouTube",title: "YouTube API Tutorial",description: "How to use the youtube API in your projects",thumbnail: "https://i.ytimg.com/vi/M7lc1UVf-VE/default.jpg",duration: 284},
            type: "YouTube",
            player: null,
            show: true,
            playing: false,
            autoplay: false,
            start_time: Math.floor(Math.random() * 1000),
        }
    },
    actions: {

        play() {
            this.playing = true;
            if (this.object.preferred_source == "YouTube") {
                this.player.playVideo();
            }
        },

        buildPlayer() {
            console.log('building player');

            //destroy player if it already exists
            if (this.player) {
                this.player.destroy();
            }

            //create player_div element inside player_div_holder
            const playerDivHolder = document.getElementById('player_div_holder');
            // create player_div element inside player_div_holder
            const playerDiv = playerDivHolder.appendChild(document.createElement('div'));
            playerDiv.id = 'player_div';
            // add h-full and w-full classes to player_div
            playerDiv.classList.add('h-full');
            playerDiv.classList.add('w-full');

            this.player = new window.YT.Player('player_div', {
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
                },
            });
        }



    }
})
