/**
 * External dependencies
 */
const gulp = require('gulp');
const svgmin = require('gulp-svgmin');
const pump = require('pump');

const config = {
	input: './assets/svg/**/*.svg',
	output: './dist/assets/svg',
};

gulp.task('svgmin', (cb) => {
	pump(
		[
			gulp.src(config.input),
			svgmin({
				plugins: [{
					removeViewBox: false,
				}],
			}),
			gulp.dest(config.output),
		],
		cb
	);
});
