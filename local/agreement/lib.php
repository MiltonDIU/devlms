<?php
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
 * @package   local_agreement
 * @copyright 2020, Md. Milton <milton2913@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
function local_agreement_extend_navigation(global_navigation $navigation) {
    global $PAGE, $CFG,$USER;
    /* Check user is admin */
    $is_admin = is_siteadmin();
    if($is_admin){
        $previewnode = $PAGE->navigation->add("Agreement", new moodle_url('/local/agreement/list.php'), navigation_node::TYPE_CUSTOM, null, null, new pix_icon('i/checked', 'A'));
        $previewnode->showinflatnavigation = true;
    }
    /* Check user is teacher */
    $is_teacher = user_has_role_assignment($USER->id,3);
    if($is_teacher){
        $previewnode = $PAGE->navigation->add("Agreement", new moodle_url('/local/agreement/form.php'), navigation_node::TYPE_CUSTOM, null, null, new pix_icon('i/checked', 'A'));
        $previewnode->showinflatnavigation = true;
    }
}