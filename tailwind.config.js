import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Http/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink:  "#060706", // log cabin black
                mint: "#B7DFD4", // primary light
                mist: "#94A6A0", // grayish
                light:"#FFFFFE", // white
                pine: "#54716C", // secondary
                aqua: "#89BBB0", // primary dark},
            },
            borderRadius: {
                    '2xl': '1rem',
            },
            boxShadow: {
                    'soft': '0 6px 24px -8px rgba(0,0,0,0.12)',
            },
        },
    },

    plugins: [forms],
};
