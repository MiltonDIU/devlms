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
 * Latest members block handling.
 *
 * @module     local_edwiserpagebuilder/remuiblck/latest_members
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
        LATEST_MEMBERS_LIST: 'local_edwiserpagebuilder/remuiblck/latestmembers_list'
    };
    var PROMISES = {
        /**
         * Get latest members promise call
         * @return {promise} ajax promise
         */
        GET_LATEST_MEMBERS_LIST: function() {
            return ajax.call([{
                methodname: 'local_edwiserpagebuilder_remuiblck_action',
                args: {
                    action: "get_latest_members_list",
                    config: ""
                }
            }])[0];
        }
    };

    /**
     * Load latest members list
     * @param {DOM}    root     block DOM object
     */
    var loadMembers = function(root) {
        PROMISES.GET_LATEST_MEMBERS_LIST().done(function(response) {
            var content = JSON.parse(response);
            var output = Templates.render(TEMPLATES.LATEST_MEMBERS_LIST, content);
            output.done(function(html) {
                $(root).html(html);
            }).fail(Notification.exception);
        }).fail(Notification.exception);
    };

    /**
     * Load latest member list on initialisation
     * @param {DOM} root block DOM object
     */
    var init = function(root) {
        $(document).ready(function() {
            loadMembers(root);
        });
    };
    return {
        init: init
    };
});
