/**
 * External dependencies
 */
const gulp = require('gulp');
const webpack = require('webpack');
const PluginError = require('plugin-error');
const log = require('fancy-log');

/**
 * Internal dependencies
 */
const config = require('../webpack.config.js');

gulp.task('webpack', (callback) => {
	// run webpack
	webpack(
		config(),
		(err, stats) => {
			if (err) {
				throw new PluginError('webpack', err);
			}

			log('[webpack]', stats.toString({
				// output options
			}));
			callback();
		}
	);
});
