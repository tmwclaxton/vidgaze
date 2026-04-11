import {defineStore} from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/inertia-vue3";
import {router} from "@inertiajs/vue3";

export const useAuthStore = defineStore('AuthStore', {
    state: () => {
        return {
            'user': null,
            'admin': false,
            'subscription_ids': [],
            'sources': [],
            'connectable_platforms': [],
            'embed_players': [],
            'showAwardDropdown': false,
            'selectedAward': null,
        }
    },

    actions: {
        toggleAwardDropdown() {
            this.showAwardDropdown = !this.showAwardDropdown;
        },
        toggleShorts() {
            // axios.patch(route('api.shorts.toggle')).then(response => {
            //     this.user.shorts_enabled = response.data.shorts_enabled;
            //     localStorage.setItem('shorts', response.data.shorts_enabled);
            // }).catch(error => {
            //     this.handleErrors(error);
            // });
            if (this.user === null) {
                const enabled = localStorage.getItem('shorts') === 'true' ? 'false' : 'true';
                localStorage.setItem('shorts', enabled);
            } else {
                axios.patch(route('api.shorts.toggle')).then(response => {
                    this.user.shorts_enabled = response.data.shorts_enabled;
                }).catch(error => {
                    this.handleErrors(error);
                });
            }
        },
        areShortsEnabled() {
            // if not logged in grab from local storage
            let shortsEnabled = false;
            if (this.user === null) {
                if (localStorage.getItem('shorts') === null) {
                    shortsEnabled = false;
                } else {
                    shortsEnabled = localStorage.getItem('shorts') === 'true';
                }
                return shortsEnabled;
            } else {
                return this.user.shorts_enabled;
            }
        },

        async getUser(toast = false) {
            const toastStore = useToastStore();
            if (!localStorage.getItem('token')) {
                return;
            }
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
            try {
                const response = await axios.get(route('api.user'));
                axios.get(route('auth.flag', {flag: true})).then(() => {}).catch(() => {});
                this.user = response.data.user;
                this.admin = response.data.admin;
                this.subscription_ids = response.data.subscription_ids;
                this.sources = response.data.sources;
                this.connectable_platforms = response.data.connectable_platforms || [];
                this.embed_players = response.data.embed_players || [];
                if (toast) {
                    toastStore.add({
                        message: 'Login successful.',
                        type: 'success',
                    });
                }
            } catch (error) {
                this.clearBrowserStorage();
                toastStore.add({
                    message: 'Sorry we couldn\'t log you in!  Please login again.',
                    type: 'warning',
                });
                throw error;
            }
        },

        async login(form) {
            const thisRef = this;
            try {
                const response = await axios.post(route('api.login'), {
                    email: form.email,
                    password: form.password,
                    remember: form.remember,
                });
                localStorage.setItem('token', response.data.access_token);
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
                await thisRef.getUser(true);
            } catch (error) {
                thisRef.handleErrors(error);
                throw error;
            }
        },

        async register(form) {
            const thisRef = this;
            try {
                const response = await axios.post(route('api.register'), {
                    username: form.username,
                    email: form.email,
                    password: form.password,
                    password_confirmation: form.password_confirmation,
                    terms: form.terms,
                });
                localStorage.setItem('token', response.data.access_token);
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
                await thisRef.getUser(true);
            } catch (error) {
                thisRef.handleErrors(error);
                throw error;
            }
        },

        logout() {
            const thisRef = this;
            return axios.post(route('api.logout')).then(async response => {
                await thisRef.clearBrowserStorage();
                // hard refresh the page
                location.reload();

                // useToastStore().add({
                //     message: 'Logout successful.',
                //     type: 'success',
                // });
            }) .catch(error => {
                thisRef.handleErrors(error);
            } );
        },

        /**
         * @param {{ email: string }} form
         * @param {{ silent?: boolean }} [options] silent: skip success toast (e.g. auth modal shows inline message)
         */
        async forgotPassword(form, { silent = false } = {}) {
            try {
                const response = await axios.post(route('api.password.email'), {
                    email: form.email,
                });
                const message = response.data.message;
                if (!silent) {
                    useToastStore().add({
                        message,
                        type: 'success',
                    });
                }
                return message;
            } catch (error) {
                this.handleErrors(error);
                throw error;
            }
        },

        async resetPassword(form) {
            const thisRef = this;
            return axios.post(route('api.password.reset'), {
                token: form.token,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
            }).then(response => {
                useToastStore().add({
                    message: response.data.message,
                    type: 'success',
                });
                thisRef.login(form).then(response => {
                    router.visit(route('home'));
                })
            }).catch(error => {
                thisRef.handleErrors(error);
            });
        },

        async clearBrowserStorage() {
            await axios.get(route('auth.flag', {flag: false})).then(response => {
            }).catch(error => {
            });
            this.user = null;
            this.admin = false;
            this.subscription_ids = [];
            this.connectable_platforms = [];
            this.embed_players = [];
            delete axios.defaults.headers.common['Authorization'];
            localStorage.removeItem('token');
        },

        handleErrors(error) {
            if (error.response === undefined) {
                return;
            }
            if (error.response.status === 422 && error.response.data?.errors) {
                return;
            }
            useToastStore().add({
                message: error.response.data.message,
                type: 'warning',
            });
        }

    },


})
