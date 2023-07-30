import { defineStore } from 'pinia'
export const useAuthStore = defineStore('AuthStore', {
    state: () => {
        return {
            'user': null,
            'admin': false,
            'subscription_ids': [],
        }
    },

    actions: {
        // check cookie for a bearer token and if it exists then get the user
        getUser() {
            if (document.cookie.includes('token')) {
                // set the bearer token in the axios header
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$cookies.get('token')
                // get the user
                axios.get('/api/user').then(response => {
                    this.user = response.data.user
                    this.admin = response.data.admin
                    this.subscription_ids = response.data.subscription_ids
                } ).catch(error => {
                    console.log(error)
                });
            }
        }



    }
})
