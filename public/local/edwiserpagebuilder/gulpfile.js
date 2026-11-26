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
 * Gulp build configuration for VvvebJs page builder styles.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Gulp.
const gulp = require('gulp');

// Sass/CSS stuff.
const gulpSass = require('gulp-sass');
const dartSass = require('sass');
const concat = require('gulp-concat');
const sass = gulpSass(dartSass);
const rename = require('gulp-rename');

gulp.task('styles', function() {
    const task = gulp.src('scss/main.scss')
    .pipe(sass({
        style: 'compressed',
    }));
    return task.pipe(rename('styles.css'))
    .pipe(gulp.dest('.'));
});

gulp.task('edweditor', function() {
    const task = gulp.src('scss/edw_editor.scss')
    .pipe(sass({
        style: 'compressed',
    }));
    return task.pipe(rename('edw_editor.css'))
    .pipe(gulp.dest('./styles'));
});

gulp.task('sass', function() {
    //gulp.src(['./html/**/*.html', '!**/_*/**'])
    return gulp.src(['./vvvebscss/*.scss'])
    .pipe(sass({
        style: 'compressed',
    }))
    .pipe(gulp.dest('./styles'));
});
