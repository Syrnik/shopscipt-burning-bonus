const {VueLoaderPlugin} = require("vue-loader");
const TerserPlugin = require("terser-webpack-plugin");
const webpack = require('webpack');

const webpackNotificationsConfig = {
    entry: "./notifications/main.js",
    output: {
        path: __dirname,
        filename: "../js/notifications-settings.js",
        libraryTarget: "umd",
        library: "BurningbonusNotificationsSettings",
        libraryExport: "default"
    },
    resolve: {
        extensions: [".js", ".vue"],
        alias: {
            "vue$": 'vue/dist/vue.runtime.esm-bundler.js'
        }
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                terserOptions: {ecma: 2016, mangle: true, output: {comments: false}},
                extractComments: false
            })
        ]
    },
    module: {
        rules: [
            {test: /\.vue$/, loader: 'vue-loader'},
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                options: {
                    presets: [
                        ['@babel/preset-env', {
                            useBuiltIns: 'usage',
                            corejs: '3.0.0',
                            debug: true,
                            targets: [
                                "last 2 Chrome versions",
                                "last 2 Firefox versions",
                                "last 2 Edge versions",
                                "last 2 Opera versions",
                                "last 2 Safari versions",
                                "last 3 iOS versions"
                            ]
                        }]
                    ],
                    plugins: ['@babel/plugin-transform-runtime']
                }
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: 'true',
            __VUE_PROD_DEVTOOLS__: 'false'
        })
    ]
};

module.exports = webpackNotificationsConfig;
