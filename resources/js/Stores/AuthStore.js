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
        }
    },

    actions: {
        getUser(toast = false) {
            const toastStore = useToastStore();
            if (localStorage.getItem('token')) {
                // we need to set the token in the axios header as this might be a page refresh
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
                axios.get(route('api.user')).then(response => {
                    this.user = response.data.user
                    this.admin = response.data.admin
                    this.subscription_ids = response.data.subscription_ids
                    this.sources = response.data.sources
                    if (toast) {
                        toastStore.add({
                            message: 'Login successful.',
                            type: 'success',
                        });
                    }
                } ).catch(error => {
                    this.clearBrowserStorage();
                    toastStore.add({
                        message: 'Sorry we couldn\'t log you in!  Please login again.',
                        type: 'warning',
                    });
                });
            } else {
            }
        },

        async login(form) {
            const toastStore = useToastStore();
            const thisRef = this;
            return axios.post(route('api.login'), {
                email: form.email,
                password: form.password,
                remember: form.remember,
            })
                .then(function (response) {
                    localStorage.setItem('token', response.data.access_token);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
                    thisRef.getUser(true);
                }).catch(error => {
                    thisRef.handleErrors(error);
                })
        },

        async register(form) {
            const toastStore = useToastStore();
            const thisRef = this;
            return axios.post(route('api.register'), {
                username: form.username,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
                terms: form.terms,
            })
                .then(function (response) {
                    localStorage.setItem('token', response.data.access_token);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
                    thisRef.getUser(true);
                }).catch(error => {
                    // console.log(error);
                    thisRef.handleErrors(error);
                })
        },

        logout() {
            const thisRef = this;
            return axios.post(route('api.logout')).then(response => {
                thisRef.clearBrowserStorage();
                useToastStore().add({
                    message: 'Logout successful.',
                    type: 'success',
                });
            }) .catch(error => {
                thisRef.handleErrors(error);
            } );
        },

        async forgotPassword(form) {
            const thisRef = this;
            return axios.post(route('api.password.email'), {
                email: form.email,
            }).then(response => {
                useToastStore().add({
                    message: response.data.message,
                    type: 'success',
                });

            }).catch(error => {
                thisRef.handleErrors(error);
            });
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

        clearBrowserStorage() {
            this.user = null;
            this.admin = false;
            this.subscription_ids = [];
            delete axios.defaults.headers.common['Authorization'];
            localStorage.removeItem('token');
        },

        handleErrors(error) {
            // console.log(error);
            if (error.response !== undefined) {
                const toastStore = useToastStore();
                useToastStore().add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            }
        }

    },


})
