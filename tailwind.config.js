import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0) rotate(0deg)' },
                    '50%': { transform: 'translateY(-20px) rotate(5deg)' },
                },
                fadeUp: {
                    from: { opacity: '0', transform: 'translateY(30px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '.6', transform: 'scale(1)' },
                    '50%': { opacity: '1', transform: 'scale(1.05)' },
                },
                scaleIn: {
                    from: { opacity: '0', transform: 'scale(.9)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                shake: {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '25%': { transform: 'translateX(-5px)' },
                    '75%': { transform: 'translateX(5px)' },
                },
                slideLeft: {
                    from: { opacity: '0', transform: 'translateX(-30px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
            },
            animation: {
                float: 'float 6s ease-in-out infinite',
                'float-slow': 'float 9s ease-in-out infinite',
                'fade-up': 'fadeUp .8s ease-out both',
                'fade-in': 'fadeIn .9s ease-out both',
                'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
                'bounce-slow': 'bounce 3s infinite',
                'scale-in': 'scaleIn .2s ease-out both',
                shake: 'shake .5s ease-in-out both',
                'slide-left': 'slideLeft .3s ease-out both',
            },
        },
    },

    safelist: [
        {
            pattern: /(bg|text)-(indigo|emerald|rose|amber|sky|fuchsia|violet|orange|slate)-(50|100|200|300|400|500|600|700|800|900)/,
        },
    ],

    plugins: [forms],
};
