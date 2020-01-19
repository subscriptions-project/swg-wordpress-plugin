/**
 * External dependencies
 */
const gulp = require('gulp');
const imagemin = require('gulp-imagemin');
const pump = require('pump');

const config = {
	input: './assets/images/*',
	output: './dist/assets/images',
};

gulp.task( 'imagemin', ( cb ) => {
	pump(
		[
			gulp.src( config.input ),
			imagemin(),
			gulp.dest( config.output ),
		],
		cb
	);
} );
