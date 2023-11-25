import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import Vue from '@vitejs/plugin-vue'
import Markdown from 'vite-plugin-vue-markdown'
import * as path from "path";
import svgLoader from 'vite-svg-loader';
import {VitePWA} from "vite-plugin-pwa";
export default defineConfig({
    plugins: [
        VitePWA({
            // injectManifest: false,
            // injectRegister: false,
            registerType: 'autoUpdate',
            devOptions: {
                enabled: true
            },

            manifest: {
                name: 'VidGaze',
                short_name: 'VidGaze',
                description: 'VidGaze is a video sharing platform',
                theme_color: '#080a0f',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                lang: 'en',
                icons: [
                    {
                        src: '/images/logos/vidgaze/app_icon_512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
        }),
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        Vue({
            include: [/\.vue$/, /\.md$/],
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            }
        }),
        svgLoader(),
        Markdown(),
    ],
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'resources'),
            '@': path.resolve(__dirname, 'resources/js'),
            '#icons': path.resolve(__dirname, 'resources/images/icons'),
        }
    }


});
