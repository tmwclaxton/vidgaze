import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import * as path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

    ],

    // check with jamie about this
    // resolve: {
    //     alias: [{
    //         '@': path.resolve(__dirname, 'resources/js'),
    //         '~': path.resolve(__dirname, 'resources'),
    //     }],
    // }
});

//do we need a production mode?
