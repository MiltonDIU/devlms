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
 * Recent feedbacks block handling.
 *
 * @module     local_edwiserpagebuilder/remuiblck/recent_feedbacks
 * @copyright  (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates'
], function(
    $,
    ajax,
    Notification,
    Templates
) {

    var TEMPLATES = {
        RECENT_FEEDBACK_LIST: 'local_edwiserpagebuilder/remuiblck/recent_assignments_list'
    };
    var PROMISES = {
        /**
         * Get recent feedbacks promise call
         * @return {promise} ajax promise
         */
        GET_RECENT_FEEDBACKS: function() {
            return ajax.call([{
                methodname: 'local_edwiserpagebuilder_remuiblck_action',
                args: {
                    action: "get_recent_feedbacks",
                    config: ""
                }
            }])[0];
        }
    };

    /**
     * Load recent feedback list
     * @param {DOM} root block DOM object
     */
    var loadRecentFeedbacks = function(root) {
        PROMISES.GET_RECENT_FEEDBACKS().done(function(response) {
            response = JSON.parse(response);

            if (response?.recentdata?.recentfeedback)
            response.recentdata.recentfeedback = Object.values(response.recentdata.recentfeedback);

            var output = Templates.render(TEMPLATES.RECENT_FEEDBACK_LIST, response);
            output.done(function(html) {
                $(root).html(html);
            }).fail(Notification.exception);
        }).fail(Notification.exception);
    };

    /**
     * Load recent feedbacks on initialisation
     * @param {DOM} root block DOM object
     */
    var init = function(root) {
        $(document).ready(function() {
            loadRecentFeedbacks(root);
        });
    };
    return {
        init: init
    };
});
