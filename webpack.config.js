/**
 * Webpack config.
 *
 * Subscribe with Google, Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const WebpackBar = require('webpackbar');

const externals = {
	react: 'React',
};

module.exports = (env, argv) => {
	return [

		// Build the settings js..
		{
			entry: {
				'subscribers': './assets/js/subscribers.js',
			},
			output: {
				filename: '[name].js',
				path: __dirname + '/dist/assets/js',
				chunkFilename: '[name].js',
				publicPath: '',
			},
			performance: {
				maxEntrypointSize: 175000,
			},
			module: {
				rules: [
					{
						test: /\.js$/,

						use: [
							{
								loader: 'babel-loader',
								query: {
									presets: [['@babel/env', {
										useBuiltIns: 'entry',
										corejs: 2,
									}], '@babel/preset-react'],
								},
							},
							{
								loader: 'eslint-loader',
								options: {
									formatter: require('eslint').CLIEngine.getFormatter('stylish'),
								},
							},
						],
					},
				],
			},
			plugins: (env && env.analyze) ? [] : [
				new WebpackBar({
					name: 'Module Entry Points',
					color: '#fbbc05',
				}),
			],
			optimization: {
				minimizer: [
					new TerserPlugin({
						parallel: true,
						sourceMap: false,
						cache: true,
						terserOptions: {
							keep_fnames: /__|_x|_n|_nx|sprintf/,
							output: {
								comments: /translators:/i,
							},
						},
						extractComments: false,
					}),
				],
				splitChunks: {
					cacheGroups: {
						default: false,
						vendors: false,

						// vendor chunk
						vendor: {
							name: 'vendor',
							chunks: 'all',
							test: /node_modules/,
							priority: 20,
						},

						// commons chunk
						commons: {
							name: 'commons',
							minChunks: 2,
							chunks: 'initial',
							priority: 10,
							reuseExistingChunk: true,
							enforce: true,
						},
					},
				},
			},
			externals,
		},

		// Build the main plugin admin css.
		{
			entry: {
				subscribers: './assets/sass/subscribers.scss',
			},
			module: {
				rules: [
					{
						test: /\.scss$/,
						use: [
							MiniCssExtractPlugin.loader,
							'css-loader',
							'sass-loader'
						],
					},
				],
			},
			plugins: (env && env.analyze) ? [] : [
				new MiniCssExtractPlugin({
					filename: '/assets/css/[name].css',
				}),
				new WebpackBar({
					name: 'Plugin CSS',
					color: '#4285f4',
				}),
			],
		},
	];
};
