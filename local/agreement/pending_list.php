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
// require config.php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE,$OUTPUT,$USER;
$PAGE->requires->css('/local/agreement/datatable/css/bootstrap.css');
$PAGE->requires->css('/local/agreement/datatable/css/dataTables.bootstrap4.min.css');
$PAGE->requires->css('/local/agreement/datatable/css/buttons.bootstrap4.min.css');
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/jquery-3.5.1.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/jquery.dataTables.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/dataTables.bootstrap4.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/dataTables.buttons.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/buttons.bootstrap4.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/jszip.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/pdfmake.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/vfs_fonts.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/buttons.html5.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/buttons.print.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/datatable/js/buttons.colVis.min.js'),true);

/* Check user is logged_in */
if (!isloggedin()) {
    redirect(get_login_url());
}
/* Check user is admin */
$is_admin = is_siteadmin();
if(!$is_admin){
    redirect($CFG->wwwroot . '/local/agreement/index.php', 'You do not have permission to view this page');
    exit();
}
$PAGE->set_url(new moodle_url('/local/agreement/pending_list.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Agreement pending teacher list');

$sql = "SELECT * FROM {user} u LEFT OUTER JOIN {local_agreements} a on u.id=a.user_id JOIN {role_assignments} r on u.id=r.userid where r.roleid=3 and a.id is null GROUP BY u.id ";
$agreements = $DB->get_records_sql($sql);
echo $OUTPUT->header();
$templatecontext = (object)[
    'agreements' => array_values($agreements),
    'heading' => "Agreement pending teacher list",
    'link' => $CFG->wwwroot . '/local/agreement/list.php'
];


echo $OUTPUT->render_from_template('local_agreement/pending_list', $templatecontext);
echo $OUTPUT->footer();

