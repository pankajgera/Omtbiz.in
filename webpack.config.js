/* eslint-disable indent */
const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ASSET_URL = process.env.ASSET_URL || '/';
const COMPRESS = (process.env.COMPRESS === "true");
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
    devtool: 'inline-cheap-module-source-map',
    entry: {
        'app': [
            path.resolve(__dirname) + '/resources/js/app.js',
            path.resolve(__dirname) + '/resources/sass/app.scss'
        ],
    },
    output: {
        publicPath: ASSET_URL,

        // Output JS filename, [name] will be the key `app-v2` from above
        filename: '[name].js',

        // Output path relative to this webpack.config.js, so here we're going up one directory and then into assets
        path: path.resolve(__dirname) + '/./public/js/',

        // use absolute paths in sourcemaps (important for debugging via IDE)
        devtoolModuleFilenameTemplate: '[absolute-resource-path]',
        devtoolFallbackModuleFilenameTemplate: '[absolute-resource-path]?[hash]'
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    module: {
        rules: [{
                test: /\.js$|jsx/,
                exclude: /node_modules/,
                use: [{
                    loader: 'babel-loader'
                }]
            },
            {
                test: /\.(woff|woff2|ttf|eot)$/,
                use: 'file-loader?name=[name].[ext]'
            }, {
                test: /\.(png|jpg|svg)$/,
                include: path.join(__dirname, 'img'),
                loader: 'url-loader?limit=30000&name=images/[name].[ext]'
            },
            {
                test: /\.vue$/,
                use: [{
                        loader: 'vue-loader'
                    },
                ]
            },
            {
                test: /\.(scss|css)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            publicPath: (resourcePath, context) => {
                                // publicPath is the relative path of the resource to the context
                                // e.g. for ./css/admin/main.css the publicPath will be ../../
                                // while for ./css/main.css the publicPath will be ../
                                return ASSET_URL;
                            },
                            sourceMap: true
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true
                        }
                    }
                ]
            }
        ]
    },
    optimization: {
        minimize: COMPRESS,
        minimizer: [new UglifyJsPlugin({ uglifyOptions: { ecma: 5, compress: { keep_fnames: true }, warnings: false, mangle: { keep_fnames: true } }, parallel: 4 })],
    },
};
