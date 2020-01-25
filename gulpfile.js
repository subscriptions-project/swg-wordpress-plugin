/**
 * Gulp config.
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

/**
 * External dependencies
 */
const gulp = require('gulp');
const requireDir = require('require-dir');
const livereload = require('gulp-livereload');

requireDir('./gulp-tasks');

/**
 * Gulp task to watch for file changes and run the associated processes.
 */
gulp.task('watch', () => {
	livereload.listen({ basePath: 'dist' });
	gulp.watch('./assets/sass/**/*.scss', gulp.series(['build']));
	gulp.watch('./assets/svg/**/*.svg', gulp.series(['build']));
	gulp.watch('./assets/js/*.js', gulp.series(['build']));
	gulp.watch('./assets/js/modules/**/*.js', gulp.series(['build']));
});

/**
 * Gulp task to build project.
 */
gulp.task('build', gulp.series(
	'webpack',
	'copy-vendor'
));

/**
 * Gulp task to run the default release processes in a sequential order.
 */
gulp.task('release', gulp.series(
	'copy-vendor'
));

/**
 * Gulp task to run the default build processes in a sequential order.
 */
gulp.task('default', gulp.series(
	'webpack',
	'copy-vendor'
));
