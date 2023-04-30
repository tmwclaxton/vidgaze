const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require("tailwindcss/colors");
const plugin = require("tailwindcss/plugin");

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    mode: 'jit',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        screens: {
            xs: "470px",
            // => @media (min-width: 640px) { ... }
            sm: "640px",
            // => @media (min-width: 640px) { ... }

            md: "768px",
            // => @media (min-width: 768px) { ... }

            ld: "868px",
            // => @media (min-width: 768px) { ... }

            lg: "1024px",
            // => @media (min-width: 1024px) { ... }

            ml: "1124px",
            // => @media (min-width: 1124px) { ... }

            xl: "1280px",
            // => @media (min-width: 1280px) { ... }

            "2xl": "1536px",
            // => @media (min-width: 1536px) { ... }

            "4xl": "1920px",
            // => @media (min-width: 1920px) { ... }
        },


        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            aspectRatio: {
                "21/12": "21 / 12",
                "9/16": "9 / 16",
                "20/27": "20 / 27",
            },
            height: {
                "10v": "10vh",
                "20v": "20vh",
                "30v": "30vh",
                "40v": "40vh",
                "50v": "50vh",
                "60v": "60vh",
                "70v": "70vh",
                "80v": "80vh",
                "90v": "90vh",
                "100v": "100vh",
            },
            colors: {
                "vidgaze-blue-nav": "rgba(9,9,9,0.9)",
                "vidgaze-blue-dropdown": "rgb(24,24,24)",
                "vidgaze-blue": "rgba(13,13,13)",
            },
            minWidth: {
                '0': '0',
                '1': '0.25rem', // 4px
                '2': '0.5rem', // 8px
                '3': '0.75rem', // 12px
                '4': '1rem', // 16px
                '5': '1.25rem', // 20px
                '6': '1.5rem', // 24px
                '8': '2rem', // 32px
                '10': '2.5rem', // 40px
                '12': '3rem', // 48px
                '16': '4rem', // 64px
                '20': '5rem', // 80px
                '24': '6rem', // 96px
                '32': '8rem', // 128px
                '40': '10rem', // 160px
                '48': '12rem', // 192px
                '56': '14rem', // 224px
                '64': '16rem', // 256px
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require("@tailwindcss/typography"),
        require("@tailwindcss/line-clamp"),
    ],
};
