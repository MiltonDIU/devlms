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
 * Task view block handling.
 *
 * @module     local_edwiserpagebuilder/remuiblck/task_view
 * @copyright  (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates',
    'local_edwiserpagebuilder/remuiblck/modal_factory',
    'core/modal_events',
    'core/fragment',
    'local_edwiserpagebuilder/remuiblck/modal_task_popup',
    'local_edwiserpagebuilder/remuiblck/events'
], function(
    $,
    ajax,
    Notification,
    Templates,
    ModalFactory,
    ModalEvents,
    Fragment,
    ModalTaskPopup,
    RemuiblckEvents
) {
    var SELECTORS = {
        TASK_CONTAINER: '[data-region="task-list"]',
        PANEL: '.panel',
        TASK_PROCESSING: '.task-processing',
        SHEDULE_TASK_BTN: '.filters [data-region="add-schedule-task"]'
    };
    var TEMPLATES = {
        TASK_LIST_ITEMS: 'local_edwiserpagebuilder/remuiblck/task-list-items'
    };
    var PROMISES = {
        /**
         * Get user tasks promise call
         * @param  {String} duration duration selection
         * @param  {String} status   status selection
         * @param  {String} search   status selection
         * @return {String} ajax promise
         */
        GET_USER_TASKS: function(duration, status, search) {
            return ajax.call([{
                methodname: 'local_edwiserpagebuilder_remuiblck_action',
                args: {
                    action: "get_user_tasks",
                    config: JSON.stringify({
                        "duration": duration,
                        "status": status,
                        "search": search
                    })
                }
            }])[0];
        }
    };

    /**
     * Load task in the tasks list
     * @param {DOM} root object
     */
    var loadTasks = function(root) {

        var duration = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'today';
        var status = arguments.length > 1 && arguments[2] !== undefined ? arguments[2] : 'all';
        var search = arguments.length > 1 && arguments[3] !== undefined ? arguments[3] : '';

        PROMISES.GET_USER_TASKS(duration, status, search).done(function(response) {
            response = JSON.parse(response);
            // response = Object.values(response);
            response.tasks = Object.values(response.tasks);


            if (response.tasks.length == 0) {
                response.no_tasks_image = M.util.image_url('empty_task_list', 'local_edwiserpagebuilder');
                $(root + " " + SELECTORS.SHEDULE_TASK_BTN).addClass("d-none");
            } else {
                $(root + " " + SELECTORS.SHEDULE_TASK_BTN).removeClass("d-none");
            }

            var output = Templates.render(TEMPLATES.TASK_LIST_ITEMS, response);

            output.done(function(html) {
                $(root).find(SELECTORS.TASK_CONTAINER).html(html);
                toggleTaskProcessing(root);
            }).fail(function(ex) {
                Notification.exception(ex);
                toggleTaskProcessing(root);
            });
        }).fail(function(ex) {
            Notification.exception(ex);
            toggleTaskProcessing(root);
        });
    };

    /**
     * Load task on initialisation
     * @param {DOM} root block DOM object
     */
    var init = function(root) {
        loadTasks(root);
    };

    /**
     * Toggle processing overlay to show that something happening in the background
     * @param  {Dom} root
     */
    var toggleTaskProcessing = function(root) {
        var show = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
        $(root).parents(SELECTORS.PANEL).find(SELECTORS.TASK_PROCESSING).toggleClass('show', show);
    };
    return {
        init: init,
        loadTasks: loadTasks,
        toggleTaskProcessing: toggleTaskProcessing
    };
});
