import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.{vue,js,ts,jsx,tsx}',
        './presets/**/*.{js,vue,ts}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: {
                    ground: '#09090b'
                }
            },
            boxShadow: {
                'input': '0 1px 2px 0px rgba(12, 17, 29, 0.05)',
                'dialog': '0 12px 24px -4px rgba(12, 17, 29, 0.10)',
                'toast': '0 4px 20px 0 rgba(12, 17, 29, 0.08)',
                'box': '0 8px 16px -4px rgba(12, 17, 29, 0.08)',
                'table': '0 2px 8px 0 rgba(12, 17, 29, 0.05)',
                'dropdown': '0px 8px 16px -4px rgba(12, 17, 29, 0.08)',
            },
        },
    },

    plugins: [forms, require('tailwindcss-primeui')],
};
