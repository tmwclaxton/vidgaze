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
                    if (toast) {
                        toastStore.add({
                            message: 'Login successful.',
                            type: 'success',
                        });
                    }
                } ).catch(error => {
                    this.handleErrors(error);
                });
            } else {
                this.clearBrowserStorage();
                toastStore.add({
                    message: 'Authentication failed.  Please login again.',
                    type: 'warning',
                });
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
                    console.log(error);
                    thisRef.handleErrors(error);
                })
        },

        logout() {
            const toastStore = useToastStore();
            const thisRef = this;
            axios.post(route('api.logout')).then(response => {
                thisRef.clearBrowserStorage();
                toastStore.add({
                    message: 'Logout successful.',
                    type: 'success',
                });
            }) .catch(error => {
                thisRef.handleErrors(error);
            } );
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
                toastStore.add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            }
        }

    },


})
