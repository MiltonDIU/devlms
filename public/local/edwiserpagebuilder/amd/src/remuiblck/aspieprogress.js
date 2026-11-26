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
 * Pie progress component for remui blocks.
 *
 * @module     local_edwiserpagebuilder/remuiblck/aspieprogress
 * @copyright  (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
"use strict";
define(['jquery', 'local_edwiserpagebuilder/remuiblck/Plugin'], function($, Plugin) {
    const NAME = 'pieProgress';

    class PieProgress extends Plugin {
        getName() {
            return NAME;
        }

        static getDefaults() {
            return {
                namespace: 'pie-progress',
                speed: 30,
                classes: {
                    svg: 'pie-progress-svg',
                    element: 'pie-progress',
                    number: 'pie-progress-number',
                    content: 'pie-progress-content'
                }
            };
        }

        render() {
            if (!$.fn.asPieProgress) {
                return;
            }

            let $el = this.$el;

            $el.asPieProgress(this.options);
        }
    }

    Plugin.register(NAME, PieProgress);

    return PieProgress;
});
