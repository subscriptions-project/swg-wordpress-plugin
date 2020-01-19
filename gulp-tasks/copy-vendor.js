/**
 * External dependencies
 */
const gulp = require('gulp');
const del = require('del');

gulp.task('copy-vendor', () => {
	del.sync(['dist/assets/vendor/*']);
	return gulp.src('assets/vendor/*')
		.pipe(gulp.dest('dist/assets/vendor/'));
});
