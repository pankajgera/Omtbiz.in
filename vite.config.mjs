import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => ({
    publicDir: 'public',
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/sass/omtbiz.scss'],
            refresh: true,
        }),
        vue(),
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
            // Map Babel runtime helper subpaths explicitly for esbuild/rollup.
            {
                find: /^@babel\/runtime\/helpers\/(.*)$/,
                replacement: path.resolve(__dirname, 'node_modules/@babel/runtime/helpers/$1.js'),
            },
            {
                find: /^@babel\/runtime\/regenerator$/,
                replacement: path.resolve(__dirname, 'node_modules/@babel/runtime/regenerator/index.js'),
            },
            {
                find: '@',
                replacement: path.resolve(__dirname, 'resources/js'),
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
                replacement: path.resolve(__dirname, 'resources/js/compat/vuelidate.js'),
            },
            {
                find: 'vuelidate/lib/validators',
                replacement: path.resolve(__dirname, 'resources/js/compat/validators.js'),
            },
        ],
        extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    optimizeDeps: {
        exclude: ['sweet-modal-vue', 'vue-tabs-component', 'vue-avatar-cropper'],
        esbuildOptions: {
            // Ensure esbuild can resolve Babel runtime helper subpaths.
            alias: {
                '@babel/runtime/helpers/slicedToArray': path.resolve(__dirname, 'node_modules/@babel/runtime/helpers/slicedToArray.js'),
                '@babel/runtime/helpers/asyncToGenerator': path.resolve(__dirname, 'node_modules/@babel/runtime/helpers/asyncToGenerator.js'),
                '@babel/runtime/regenerator': path.resolve(__dirname, 'node_modules/@babel/runtime/regenerator/index.js'),
            },
        },
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
