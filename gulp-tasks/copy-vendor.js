/**
 * External dependencies
 */
import gulp from 'gulp';
import del from 'del';

gulp.task('copy-vendor', () => {
	del.sync(['dist/assets/vendor/*']);
	return gulp.src('assets/vendor/*')
		.pipe(gulp.dest('dist/assets/vendor/'));
});
