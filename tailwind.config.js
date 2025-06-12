import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                matisse: {
                    50: '#f4f7fb',
                    100: '#e9eef5',
                    200: '#cddcea',
                    300: '#a1bed8',
                    400: '#6f9cc1',
                    500: '#4d80aa',
                    600: '#3b6790',
                    700: '#305274',
                    800: '#2b4661',
                    900: '#283d52',
                    950: '#1b2736',
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
            fontSize: {
                xxs: ['10px', '16px'],
                xs: ['12px', '18px'],
                sm: ['14px', '20px'],
                base: ['16px', '24px'],
                lg: ['20px', '28px'],
                xl: ['24px', '32px'],
                xxl: ['30px', '42px'],
            },
            screens: {
                'xs': '320px',
                'sm': '640px',
                'md': '768px',
                'lg': '1024px',
                'xl': '1280px',
                '2xl': '1536px',
                '3xl': '1792px',
            },
        },
    },

    plugins: [forms, require('tailwindcss-primeui')],
};
