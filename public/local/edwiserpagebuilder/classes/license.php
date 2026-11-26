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
 * License management page
 *
 * @package   local_edwiserpagebuilder
 * @copyright Copyright (c) 2024 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('license_controller.php');

global $PAGE, $CFG, $OUTPUT, $DB;
admin_externalpage_setup('local_edwiserpagebuilder_licensestatus');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$title = get_string('licensestatus', 'local_edwiserpagebuilder');
$PAGE->set_title($title);
$PAGE->set_heading(get_string('licensestatus', 'local_edwiserpagebuilder'));
$PAGE->set_url($CFG->wwwroot . "/local/edwiserpagebuilder/classes/license.php");

if (!is_siteadmin()) {
    return false;
}

$pluginslug = 'edwiser-page-builder-for-moodle';
$lcontroller = new edwiserpagebuilder_license_controller();
$lcontroller->add_data();
$licensekey = get_config('local_edwiserpagebuilder', 'edd_' . $pluginslug . '_license_key');
$licensekey = get_config('local_edwiserpagebuilder', 'edd_' . $pluginslug . '_license_key');
if (empty($licensekey)) {
    $licensekey = '';
}

// Get License Status.
$lastaction = optional_param('lastaction', false, PARAM_ALPHA);
$status = get_config('local_edwiserpagebuilder', 'edd_' . $pluginslug . '_license_status');

// Get renew link.
$renewlink = get_config('local_edwiserpagebuilder', 'wdm_' . $pluginslug . '_product_site');

$cfglicensekey = get_config('local_edwiserpagebuilder', 'licensekey');
$licensekeyactivate = get_config('local_edwiserpagebuilder', 'licensekeyactivate');
$licensekeydeactivate = get_config('local_edwiserpagebuilder', 'licensekeydeactivate');

// Build alert messages.
$alerts = [];
if ($cfglicensekey == 'empty') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('error', 'local_edwiserpagebuilder'),
        'message' => get_string('enterlicensekey', 'local_edwiserpagebuilder'),
    ];
}
if (
    $lastaction !== false && $lastaction == 'valid'
    && optional_param('licensekeyactivate', false, PARAM_ALPHA) != false
) {
    $alerts[] = [
        'type' => 'success',
        'icon' => 'check',
        'title' => get_string('success', 'local_edwiserpagebuilder'),
        'message' => get_string('licensekeyactivated', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction !== false && $lastaction == 'expired') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('licensekeyhasexpired', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction !== false && $lastaction == 'disabled') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('licensekeyisdisabled', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction == 'invalid') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('entervalidlicensekey', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction == 'noactivationsleft') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('nolicenselimitleft', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction == 'site_inactive') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('siteinactive', 'local_edwiserpagebuilder'),
    ];
} else if ($lastaction == 'deactivated') {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('licensekeydeactivated', 'local_edwiserpagebuilder'),
    ];
} else if (
    $lastaction == 'no_response'
    || (optional_param('licensekeydeactivate', false, PARAM_ALPHA) != false && $lastaction == 'valid')
) {
    $alerts[] = [
        'type' => 'danger',
        'icon' => 'ban',
        'title' => get_string('alert', 'local_edwiserpagebuilder'),
        'message' => get_string('noresponsereceived', 'local_edwiserpagebuilder'),
    ];
}

// Remove config vars.
unset_config('licensekey', 'local_edwiserpagebuilder');
unset_config('licensekeyactivate', 'local_edwiserpagebuilder');
unset_config('licensekeydeactivate', 'local_edwiserpagebuilder');

$sesskeyhidden = sesskey();
$activationtitle = get_string('edwiserpagebuilderlicenseactivation', 'local_edwiserpagebuilder');
$licensekeystr = get_string('licensekey', 'local_edwiserpagebuilder');

// Check if RemUI Pro license is active.
require_once($CFG->dirroot . '/local/edwiserpagebuilder/lib.php');
$remuiproactive = false;
if (local_edwiserpagebuilder_check_plugin_available('theme_remui')) {
    if (!defined('THEME_REMUI_PLUGINNAME') && file_exists($CFG->dirroot . '/theme/remui/classes/controller/LicenseController.php')) {
        require_once($CFG->dirroot . '/theme/remui/classes/controller/LicenseController.php');
    }
    if (defined('THEME_REMUI_PLUGINNAME') && THEME_REMUI_PLUGINNAME === 'Edwiser RemUI') {
        $remuistatus = get_config('theme_remui', 'edd_remui_license_status');
        if ($remuistatus == 'valid') {
            $remuiproactive = true;
        }
    }
}
$disabledattr = $remuiproactive ? 'disabled' : '';

// Determine input state.
if ($status == 'valid') {
    $licenseinputclass = 'has-success';
    $readonly = true;
} else if ($status == 'expired') {
    $licenseinputclass = 'has-error';
    $readonly = true;
} else {
    $licenseinputclass = 'has-error';
    $readonly = false;
}

$placeholder = get_string('enterlicensekeyplaceholder', 'local_edwiserpagebuilder');

// Determine status text.
$statustextactive = get_string('active', 'local_edwiserpagebuilder');
$statustextinactive = get_string('notactive', 'local_edwiserpagebuilder');
$statustextexpired = get_string('expired', 'local_edwiserpagebuilder');
$statustextlimitexceeded = get_string('no_activations_left', 'local_edwiserpagebuilder');

if ($remuiproactive || ($status !== false && $status == 'valid')) {
    $statustext = "<p style='color:green;'>{$statustextactive}</p>";
} else if ($status == 'site_inactive') {
    $statustext = "<p style='color:red;'>{$statustextinactive}</p>";
} else if ($status == 'expired') {
    $statustext = "<p style='color:red;'>{$statustextexpired}</p>";
} else if ($status == 'invalid') {
    $statustext = "<p style='color:red;'>{$statustextinactive}</p>";
} else if ($status == 'no_activations_left') {
    $statustext = "<p style='color:red;'>{$statustextlimitexceeded}</p>";
} else {
    $statustext = "<p style='color:red;'>{$statustextinactive}</p>";
}

// Determine buttons to show.
$showdeactivate = ($status !== false && $status == 'valid');
$showrenew = ($status == 'expired');
$showactivate = (!$showdeactivate && !$showrenew);

$activatelicensetext = get_string('activatelicense', 'local_edwiserpagebuilder');
$deactivatelicensetext = get_string('deactivatelicense', 'local_edwiserpagebuilder');
$renewlicensetext = get_string('renewlicense', 'local_edwiserpagebuilder');

$templatedata = [
    'alerts' => $alerts,
    'sesskey' => $sesskeyhidden,
    'activationtitle' => $activationtitle,
    'remuiproactive' => $remuiproactive,
    'remuiprotext' => get_string('remuiprolicense', 'local_edwiserpagebuilder'),
    'pluginslug' => $pluginslug,
    'licensekeystr' => $licensekeystr,
    'licensekey' => $licensekey,
    'placeholder' => $placeholder,
    'licenseinputclass' => $licenseinputclass,
    'readonly' => $readonly,
    'disabledattr' => $disabledattr,
    'licensestatusstr' => get_string('licensestatus', 'local_edwiserpagebuilder'),
    'statustext' => $statustext,
    'showdeactivate' => $showdeactivate,
    'showrenew' => $showrenew,
    'showactivate' => $showactivate,
    'activatelicensetext' => $activatelicensetext,
    'deactivatelicensetext' => $deactivatelicensetext,
    'renewlicensetext' => $renewlicensetext,
    'renewlink' => $renewlink,
];

$out = $OUTPUT->render_from_template('local_edwiserpagebuilder/license_page', $templatedata);
$onlicensepage = optional_param('onRapidGraderLicensePage', false, PARAM_BOOL);
if ($onlicensepage) {
    $url = new moodle_url('/local/edwiserpagebuilder/classes/license.php', [
        'lastaction' => $status,
        'licensekeyactivate' => $licensekeyactivate,
        'licensekeydeactivate' => $licensekeydeactivate,
    ]);
    redirect($url->out());
}
echo $OUTPUT->header();
echo $out;
echo $OUTPUT->footer();
