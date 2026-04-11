import { defineStore } from 'pinia';

export const useAuthModalStore = defineStore('AuthModal', {
    state: () => ({
        show: false,
        /** @type {'login' | 'register' | 'forgot_password'} */
        panel: 'login',
        /** @type {null | (() => void | Promise<void>)} */
        pendingCallback: null,
    }),
    actions: {
        /**
         * @param {'login' | 'register'} initialPanel
         * @param {null | (() => void | Promise<void>)} pendingCallback runs after successful auth
         */
        open(initialPanel = 'login', pendingCallback = null) {
            this.panel = initialPanel;
            this.pendingCallback = pendingCallback;
            this.show = true;
        },
        close() {
            this.show = false;
            this.pendingCallback = null;
            this.panel = 'login';
        },
        /** @param {'login' | 'register' | 'forgot_password'} panel */
        setPanel(panel) {
            this.panel = panel;
        },
        async resolvePendingAfterAuth() {
            const cb = this.pendingCallback;
            this.pendingCallback = null;
            this.show = false;
            if (typeof cb === 'function') {
                await cb();
            }
        },
    },
});
