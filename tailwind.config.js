import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#7c3bed',
                'background-light': '#f7f6f8',
                'background-dark': '#171121',
            },
            fontFamily: {
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                DEFAULT: '0.5rem',
                lg: '1rem',
                xl: '1.5rem',
                '2xl': '2rem',
                full: '9999px',
            },
        },
    },

    plugins: [forms],
};
