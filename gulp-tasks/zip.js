/**
 * External dependencies
 */
const gulp = require('gulp');
const zip = require('gulp-zip');
const del = require('del');

gulp.task('pre-zip', () => {
	del.sync(['./release/subscribe-with-google/**']);

	return gulp.src('release/**')
		.pipe(gulp.dest('release/subscribe-with-google/'));
});

gulp.task('zip', () => {
	gulp.src(
		['release/subscribe-with-google/**'],
		{ base: 'release/' }
	)
		.pipe(zip('subscribe-with-google.zip'))
		.pipe(gulp.dest('./'));
});

gulp.task('pre-zip-wp50', () => {
	del.sync(['./release/subscribe-with-google-wp50/**']);

	return gulp.src([
		'release/**',
		'!release/dist/assets/vendor',
		'!release/dist/assets/vendor/**',
		'!release/dist/assets/js/externals/!(svgxuse.js)',
	])
		.pipe(gulp.dest('release/subscribe-with-google-wp50/'));
});

gulp.task('zip-wp50', () => {
	gulp.src(
		['release/subscribe-with-google-wp50/**'],
		{ base: 'release/' }
	)
		.pipe(zip('subscribe-with-google-wp50.zip'))
		.pipe(gulp.dest('./'));
});

