import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import Vue from '@vitejs/plugin-vue'
import Markdown from 'vite-plugin-vue-markdown'
import * as path from "path";
import svgLoader from 'vite-svg-loader';
export default defineConfig({
    plugins: [

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
