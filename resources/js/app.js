import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createPinia } from "pinia";
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Link } from '@inertiajs/vue3';
import VueClickAway from "vue3-click-away";
// import VueYouTubeEmbed from 'vue-youtube-embed';
/* import the fontawesome core - utility functions*/
import { library } from '@fortawesome/fontawesome-svg-core';
/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {fas } from '@fortawesome/free-solid-svg-icons'
import {far } from '@fortawesome/free-regular-svg-icons'
import {fab } from '@fortawesome/free-brands-svg-icons'
import { dom } from '@fortawesome/fontawesome-svg-core'
library.add(fas, far, fab);

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title ? `${title} - ` : ''}${appName ?? 'Laravel'}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(createPinia())
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(VueClickAway)
            .component('font-awesome-icon', FontAwesomeIcon)
            .component('Link', Link)
            .mount(el);
    },
    progress: {
        color: '#4b93ff',
    },
});
