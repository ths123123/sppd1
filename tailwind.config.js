import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-gradient-to-r',
        'from-slate-800',
        'via-slate-900',
        'to-indigo-900',
        'bg-[#3E0050]',
        'border-[#4E1060]',
        'bg-[#540070]',
        'login-page',
        'login-container',
        'text-grok-white',
        'text-grok-muted',
        'bg-grok-dark',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                emerald: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#27a269', // Custom KPU Emerald Green
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                },
            },
        },
    },
    variants: {
        extend: {
            display: ['group-hover'],
            visibility: ['group-hover'],
        },
    },

    plugins: [forms],
};
