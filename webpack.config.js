const autoPrefixer = require('autoprefixer');
const extractTextPlugin = require('extract-text-webpack-plugin');
const mediaQueryPacker = require('css-mqpacker');
const webpack = require('webpack');
const browsers = require('./browsers.json');

const isProduction = 'production' === process.env.NODE_ENV;
const cssFileExtension = isProduction ? '.min.css' : '.css';
const jsFileExtension = isProduction ? '.min.js' : '.js';

const extractCSS = new extractTextPlugin('assets/css/[name]' + cssFileExtension);

const config = {
    entry: {
        'wp-post-expiration': [
            './source/scss/wp-post-expiration.scss',
            './source/js/wp-post-expiration.js',
            './node_modules/flatpickr/dist/flatpickr.css',
            './node_modules/flatpickr/dist/plugins/confirmDate/confirmDate.css',
        ]
    },
    output: {
        filename: 'assets/js/[name]' + jsFileExtension,
        path: __dirname
    },
    externals: {
        'jquery': 'jQuery'
    },
    resolve: {
        extensions: ['.js', '.jsx', '.json'],
        modules: [
            'node_modules'
        ]
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.css$/,
                use: extractCSS.extract({
                    fallback: 'style-loader',
                    use: [
                        {
                            loader: 'css-loader',
                            options: {sourceMap: true}
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                sourceMap: true,
                                plugins: [
                                    autoPrefixer({browsers: browsers}),
                                    mediaQueryPacker()
                                ]
                            }
                        }
                    ]

                })
            },
            {
                test: /\.scss$/,
                use: extractCSS.extract({
                    fallback: 'style-loader',
                    use: [
                        {
                            loader: 'css-loader',
                            options: {sourceMap: true}
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                sourceMap: true,
                                plugins: [
                                    autoPrefixer({browsers: browsers}),
                                    mediaQueryPacker()
                                ]
                            }
                        },
                        {
                            loader: 'sass-loader',
                            options: {sourceMap: true}
                        }
                    ]

                })
            },
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                loader: 'babel-loader',
                query: {
                    presets: [
                        ['babel-preset-env', {modules: false, targets: {browsers: browsers}}]
                    ],
                    plugins: [
                        'transform-class-properties',
                        'transform-object-rest-spread'
                    ]
                }
            }
        ]
    },
    plugins: [
        extractCSS,
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development')
        })
    ]
};

module.exports = config;