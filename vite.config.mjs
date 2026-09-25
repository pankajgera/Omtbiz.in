import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

export default defineConfig(({ mode }) => ({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/sass/omtbiz.scss', 'resources/css/tailwind.css'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        'process.env': {
            NODE_ENV: mode,
        },
    },
    resolve: {
        alias: [
            // Keep the legacy avatar cropper's Babel helper imports resolvable.
            {
                find: /^@babel\/runtime\/helpers\/(.*)$/,
                replacement: path.resolve(import.meta.dirname, 'node_modules/@babel/runtime/helpers/$1.js'),
            },
            {
                find: /^@babel\/runtime\/regenerator$/,
                replacement: path.resolve(import.meta.dirname, 'node_modules/@babel/runtime/regenerator/index.js'),
            },
            {
                find: '@',
                replacement: path.resolve(import.meta.dirname, 'resources/js'),
            },
            {
                find: 'vue',
                replacement: '@vue/compat',
            },
            {
                find: 'vue$',
                replacement: '@vue/compat',
            },
            {
                find: 'vuelidate',
                replacement: path.resolve(import.meta.dirname, 'resources/js/compat/vuelidate.js'),
            },
            {
                find: 'vuelidate/lib/validators',
                replacement: path.resolve(import.meta.dirname, 'resources/js/compat/validators.js'),
            },
        ],
        extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    optimizeDeps: {
        exclude: ['sweet-modal-vue', 'vue-tabs-component', 'vue-avatar-cropper'],
    },
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                silenceDeprecations: ['import', 'color-functions', 'global-builtin', 'if-function', 'slash-div'],
                logger: {
                    warn(message, options) {
                        const deprecationType = options?.deprecationType?.id;
                        if (
                            deprecationType === 'import' ||
                            deprecationType === 'color-functions' ||
                            deprecationType === 'global-builtin' ||
                            deprecationType === 'if-function' ||
                            deprecationType === 'slash-div'
                        ) {
                            return;
                        }
                        if (typeof message === 'string' && message.includes('Sass @import rules are deprecated')) {
                            return;
                        }
                        console.warn(message);
                    },
                },
            },
        },
    },
}));
