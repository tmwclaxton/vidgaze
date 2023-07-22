import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createPinia } from "pinia";
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Layout from './Layouts/AuthenticatedLayout.vue';
import VueClickAway from "vue3-click-away";
import VueHoneypot from 'vue-honeypot'
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
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        );
        page.then((module) => {
            const page = module.default;
            let layout = page.layout;

            if (layout === undefined) {
                layout = Layout;
            }
            page.layout = layout;
        });
        return page;
    },
    // resolve: (name) => resolvePageComponent(
    //     `./Pages/${name}.vue`,
    //     import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(createPinia())
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(VueClickAway)
            .component('VueHoneyPot', VueHoneypot)
            .component('font-awesome-icon', FontAwesomeIcon)
            .component('Link', Link)
            .component('Head', Head)
            .mount(el);
    },
    progress: {
        color: '#4b93ff',
    },
});
