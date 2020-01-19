/**
 * External dependencies
 */
const gulp = require('gulp');
const svgstore = require('gulp-svgstore');
const svgmin = require('gulp-svgmin');
const pump = require('pump');

const config = {
	input: './assets/svg/**/*.svg',
	output: './dist/assets/svg',
};

gulp.task('svgstore', (cb) => {
	pump(
		[
			gulp.src(config.input),
			svgmin({
				plugins: [{
					removeViewBox: false,
				}],
			}),
			svgstore({ inlineSvg: true }),
			gulp.dest(config.output),
		],
		cb
	);
});
