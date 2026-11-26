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
 * Event handling for remui blocks.
 *
 * @module     local_edwiserpagebuilder/remuiblck/events
 * @copyright  (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        TASK_SAVE: 'modal_task_popup:save',
        TASK_DELETE: 'modal_task_popup:delete',
        TASK_CANCEL: 'modal_task_popup:cancel',
        TASK_DURATION_FILTER_CHANGE: 'task-duration-filter-change',
        TASK_STATUS_FILTER_CHANGE: 'task-status-filter-change',
        TASK_SEARCH_FILTER_CHANGE: 'input',
        MANAGE_COURSES_DISPLAY_FILTER_CHANGE: 'manage-course-display-filter-change',
        MANAGE_COURSES_PAGE_FILTER_CHANGE: 'manage-course-page-filter-change',
        MANAGE_COURSES_PAGINATE: 'manage-course-paginate',
        COURSE_ANALYTICS_PAGE_FILTER_CHANGE: 'course-analytics-page-filter-change'
    };
});
