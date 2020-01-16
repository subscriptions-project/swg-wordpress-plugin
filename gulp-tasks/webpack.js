/**
 * External dependencies
 */
import gulp from 'gulp';
import webpack from 'webpack';
import PluginError from 'plugin-error';
import log from 'fancy-log';

/**
 * Internal dependencies
 */
import config from '../webpack.config.js';

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
