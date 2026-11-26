// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Customizer page.
 *
 * @package   theme_remui
 * @copyright (c) 2022 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Gulp.
var gulp = require('gulp');

// Sass/CSS stuff.
var gulpSass = require('gulp-sass'),
    dartSass = require('sass'),
    exec = require('gulp-exec'),
    notify = require("gulp-notify"),
    gulpStylelint = require('gulp-stylelint'),
    mediaGroup = require('gulp-group-css-media-queries'),
    mediaMerge = require('gulp-merge-media-queries'),
    cleanCSS = require('gulp-clean-css'),
    frep = require('gulp-frep'),
    sass = gulpSass(dartSass);

// Check production mode.
var PRODUCTION = process.argv.includes('-production');

// Pattern for newline replacement for windows development environment.
var pattern = [{
    pattern: /\\r\\n/g,
    replacement: '\\n'
}];

var baseDir = '../..';

gulp.task('lint-styles', function lintStyles() {
    return gulp.src('scss/**/*.scss')
        .pipe(gulpStylelint({
            reporters: [
                {
                    formatter: 'string',
                    console: true
                }
            ]
        }));
});

gulp.task('fix-styles', function fixCssTask() {
    return gulp
        .src('scss/**/*.scss')
        .pipe(gulpStylelint({
            fix: true
        }))
        .pipe(gulp.dest('scss'));
});

gulp.task('otherstyles', function() {
    var task = gulp.src('scss/other/**/*.scss')
        .pipe(sass({
            outputStyle: 'compressed',
        }));
    if (PRODUCTION) {
        task = task.pipe(mediaMerge())
            .pipe(mediaGroup());
    }

    task = task.pipe(cleanCSS({
        compatibility: 'ie8'
    }));

    if (PRODUCTION) {
        task = task.pipe(frep(pattern));
    }
    return task.pipe(gulp.dest('./style/'));
});

gulp.task('purge', function() {
    return gulp.src('.')
        .pipe(exec('php8.0 ' + baseDir + '/admin/cli/purge_caches.php'))
        .pipe(notify('Purged All'));
});

gulp.task('purgejs', function() {
    return gulp.src('.')
        .pipe(exec('php8.0 ' + baseDir + '/admin/cli/purge_caches.php --js=true'))
        .pipe(notify('Purged Language Packs'));
});

gulp.task('purgelang', function() {
    return gulp.src('.')
        .pipe(exec('php8.0 ' + baseDir + '/admin/cli/purge_caches.php --lang=true'))
        .pipe(notify('Purged Js'));
});

gulp.task('watchscss', function(done) {
    gulp.watch([
        './scss/**/*.scss',
        './scss/**/*.css',
        '!./scss/other/*.scss'
    ], gulp.series('purge'));
    gulp.watch([
        './scss/other/*.scss'
    ], gulp.series('otherstyles'));
    gulp.watch([
        './lang/**/*.php',
        './templates/**/*'
    ], gulp.series('purge'));
    done();
});

gulp.task('watchjs', function(done) {
    gulp.watch(['amd/build/**/*.js'], gulp.series('purge'));
    done();
});

gulp.task('watchlang', function(done) {
    gulp.watch(['lang/**/*.php'], gulp.series('purgelang'));
    done();
});

gulp.task('watchlintstyles', function(done) {
    gulp.watch('scss/**/*.scss', gulp.series('lint-styles'));
    done();
});

gulp.task('lintstyles', gulp.series('watchlintstyles', 'lint-styles'));

gulp.task('watch', gulp.series('watchscss', 'watchjs', 'watchlang'));

gulp.task('scss', gulp.series('otherstyles', 'purge', 'watchscss'));

gulp.task('default', gulp.series('otherstyles', 'purge', 'watch'));
