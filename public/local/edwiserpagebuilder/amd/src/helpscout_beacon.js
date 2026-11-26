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
 * Helpscout Beacon loader for free-version users.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        /**
         * Initialise the Helpscout Beacon widget.
         */
        init: function() {
            /* eslint-disable */
            !function(e,t,n){function a(){var e=t.getElementsByTagName('script')[0],
            n=t.createElement('script');n.type='text/javascript',n.async=!0,
            n.src='https://beacon-v2.helpscout.net',e.parentNode.insertBefore(n,e)}
            if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},
            n.readyQueue=[],'complete'===t.readyState)return a();
            e.attachEvent?e.attachEvent('onload',a):e.addEventListener('load',a,!1)}
            (window,document,window.Beacon||function(){});
            window.Beacon('init', '8099719e-d2d1-45db-ae26-e326c9fd3c08');
            /* eslint-enable */
        }
    };
});
