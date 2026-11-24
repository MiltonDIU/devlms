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
 * Strings for the quizaccess_proctoringlms plugin.
 *
 * @package    quizaccess_proctoringlms
 * @author     Kamrul Islam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$string['proctoringrequiredlms_help'] = 'If you enable this option, students will not be able to start an attempt until they have ticked a check-box confirming that they are aware of the policy on webcam.';
//$string['proctoringrequiredoption'] = 'must be acknowledged before starting an attempt';
$string['proctoringrequiredoption'] = 'Enable';
$string['notrequired'] = 'not required';
$string['privacy:metadata'] = 'We do not share any personal data with third parties.';
$string['proctoringheader'] = '<strong>To continue with this assessment, you must allow permission to your webcam access, and it will take periodic pictures during the assessment.</strong>';
$string['proctoringheader_set'] = 'To continue with this assessment, you must allow permission to your webcam access, and it will take periodic pictures during the assessment.';
$string['proctoringlabel'] = 'I agree with the validation process.';
$string['proctoringstatement'] = 'This exam requires webcam access for proctoring purpose. You must allow the webcam access and it will take periodic pictures during the quiz.'; // <br />(Please allow access to your webcam or device camera).
$string['camhtml'] = '<div class="camera"> <video id="video">Video stream not available.</video></div> <canvas id="canvas" style="display:none;"> </canvas> <img style="display:none;" id="photo" alt="The screen capture will appear in this box."/>';
$string['pluginname'] = 'Proctoring';
$string['quizaccess_proctoring'] = 'Quizaccess Proctoring';
$string['youmustagree'] = 'You must agree to validate your identity before continue.';
//$string['youmustagree'] = 'Enable';
$string['proctoringrequiredlms'] = 'Remote Proctoring Enable';
$string['notpermissionreport'] = 'Identity validation reports are disabled for you.';
$string['eprotroringreports'] = 'Identity validation report for: ';
$string['eprotroringreportsdesc'] = 'This report accumulates all the images of the student which are taken during the exam along with other warning actions (if any). You can utilize these records to validate the student identity, and examination integrity.';
$string['status'] = 'Validation status';
$string['dateverified'] = 'Date and time';
$string['actions'] = 'Actions';
$string['picturesreport'] = 'View Proctoring Report';
$string['webcampicture'] = 'webcampicture';
$string['picturesusedreport'] = 'These are the captured pictures taken during the assessment.';
$string['setting:proctoringreconfigureproctoring'] = 'Auto-configure Proctoring';
$string['setting:proctoringreconfigureproctoring_desc'] = 'If enabled, users who navigate to the quiz take webcam pictures';
$string['setting:autoreconfigureproctoring_desc'] = 'If enabled, users who navigate to the quiz take webcam pictures';
$string['setting:autoreconfigureproctoring'] = 'Auto-configure Proctoring Assessment';
$string['event:takescreenshot'] = 'Taken a screenshot';
$string['event:screenshotcreated'] = 'A new screenshot was created';
$string['event:screenshotupdated'] = 'Screenshot was updated';


$string['privacy:metadata:courseid'] = 'The ID of the course that use proctoring.';
$string['privacy:metadata:quizid'] = 'The ID of the Quiz that use proctoring.';
$string['privacy:metadata:webcampicture'] = 'The name of picture that has been taken by the proctoring.';
$string['privacy:metadata:status'] = 'The Status of the proctoring.';
$string['timemodified'] = 'Last modified';
$string['privacy:metadata:quizaccess_proctoring_logs'] = 'Moodle Quiz access Proctoring logs table that store user\'s picture.';

$string['proctoringlms:sendcamshot'] = 'Proctoring send webcam photo';
$string['proctoringlms:getcamshots'] = 'Proctoring get webcam photos';
$string['proctoringlms:viewreport'] = 'Proctoring view report';
$string['proctoringlms:sendrightclick'] = 'Proctoring Right click';
$string['proctoringlms:sendscheenclick'] = 'Proctoring Scheen Share click';
$string['name'] = 'Student Name';
$string['scheenshare'] = 'Screen share';
$string['webcampicture'] = 'Captured Pictures';
$string['openwebcam'] = 'Allow your webcam to continue';

$string['privacy:quizaccess_proctoring_logs'] = 'QuizAccess Proctoring logs';
$string['privacy:core_files'] = 'QuizAccess Proctoring webcam pictures';
$string['privacy:metadata:core_files'] = 'The Quiz Access stores users picture which has been shot by the webcam during quiz attempt.';

