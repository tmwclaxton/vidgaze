import { defineStore } from 'pinia'
export const useContentModalStore = defineStore('ContentModalStore', {
    state: () => {
        return {
            itemId: null,
            showMenu: false,
            x: 0,
            y: 0,
            widthOfMenu: 0,
            heightOfMenu: 0,
        }
    },
    actions: {
        setItemId(id) {
            this.itemId = id;
        },
        setMenuShow(value) {
            this.showMenu = value;
            // menu can't hidden or else can't find it to get width and height
            // update doesn't seem to work
            const menuRef = document.getElementById("menu");
            if (menuRef !== null) {
                this.setMenuSize(menuRef.offsetWidth, menuRef.offsetHeight);
            }
            this.setCoordinates();

        },
        setCoordinates() {
            if (this.itemId !== null) {
                const buttonRect = document.getElementById('dotsButton_' + this.itemId).getBoundingClientRect();
                this.y = buttonRect.top + window.scrollY + 37;
                this.x = buttonRect.left + window.scrollX ;

                // Adjust x if menu goes off right edge of screen
                if (this.x + this.widthOfMenu > window.innerWidth) {
                    this.x -= this.widthOfMenu;
                }
            }
        },
        setMenuSize(width, height) {
            this.widthOfMenu = width;
            this.heightOfMenu = height;
        },
        getX() {
            return this.x
        },
        getY() {
            return this.y
        },
        getItemId() {
            return this.itemId
        },
        getMenuShow() {
            return this.showMenu
        }
    }
})
