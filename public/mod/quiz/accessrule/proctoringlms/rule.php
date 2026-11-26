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
 * Implementaton for the quizaccess_proctoringlms plugin.
 *
 * @package    quizaccess_proctoringlms
 * @author     Kamrul Islam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

// Moodle 5.3 port: accessrulebase.php / quiz_access_rule_base / quiz / mod_quiz_preflight_check_form
// were removed; use the namespaced 4.2+ quiz API instead.
use mod_quiz\local\access_rule_base;
use mod_quiz\form\preflight_check_form;
use mod_quiz\quiz_settings;

// For Development $CFG->cachejs = false.

/**
 * quizaccess_proctoringlms
 */
class quizaccess_proctoringlms extends access_rule_base
{

    /**
     * Check is preflight check is required.
     *
     * @param mixed $attemptid
     * @return bool
     */
    public function is_preflight_check_required($attemptid) {
        return empty($attemptid);
    }

    /**
     * add_preflight_check_form_fields
     *
     * @param preflight_check_form $quizform
     * @param MoodleQuickForm $mform
     * @param mixed $attemptid
     * @return void
     * @throws coding_exception
     */
    public function add_preflight_check_form_fields(preflight_check_form $quizform, MoodleQuickForm $mform, $attemptid) {
        $mform->addElement('header', 'proctoringheader', get_string('openwebcam', 'quizaccess_proctoringlms'));
//        $mform->addElement('static', 'proctoringmessage', '', get_string('proctoringstatement', 'quizaccess_proctoringlms'));
        $mform->addElement('static', 'cammessage', '', get_string('camhtml', 'quizaccess_proctoringlms'));
        $mform->addElement('checkbox', 'proctoring', '', get_string('proctoringlabel', 'quizaccess_proctoringlms'));
    }

    /**
     * Validate the preflight check
     *
     * @param mixed $data
     * @param mixed $files
     * @param mixed $errors
     * @param mixed $attemptid
     * @return mixed $errors
     * @throws coding_exception
     */
    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        if (empty($data['proctoring'])) {
            $errors['proctoring'] = get_string('youmustagree', 'quizaccess_proctoringlms');
        }

        return $errors;
    }

    /**
     * * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @param quiz_settings $quizobj
     * @param int $timenow
     * @param bool $canignoretimelimits
     * @return access_rule_base|quizaccess_proctoringlms|null
     */
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->proctoringrequiredlms)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from mod_quiz_mod_form::definition(), while the
     * security section is being built.
     *
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     * @throws coding_exception
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $mform->addElement('select', 'proctoringrequiredlms',
            get_string('proctoringrequiredlms', 'quizaccess_proctoringlms'),
            array(
                0 => get_string('notrequired', 'quizaccess_proctoringlms'),
                1 => get_string('proctoringrequiredoption', 'quizaccess_proctoringlms'),
            ));
        $mform->addHelpButton('proctoringrequiredlms', 'proctoringrequiredlms', 'quizaccess_proctoringlms');
    }
    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from quiz_after_add_or_update() in lib.php.
     *
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     * @throws dml_exception
     */
    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->proctoringrequiredlms)) {
            $DB->delete_records('quizaccess_proctoringlms', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('quizaccess_proctoringlms', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->proctoringrequiredlms = 1;
                $DB->insert_record('quizaccess_proctoringlms', $record);
            }
        }
    }
    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from quiz_delete_instance() in lib.php.
     *
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @throws dml_exception
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_proctoringlms', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of quiz_access_manager::load_settings().
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the get_extra_settings() method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array(
            'proctoringrequiredlms',
            ' LEFT JOIN {quizaccess_proctoringlms} proctoringlms ON proctoringlms.quizid = quiz.id ',
            array());
    }

    /** ec2, rds,
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @return mixed a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     * @throws coding_exception
     */
    public function description() {
        global $PAGE, $CFG;
        $CFG->cachejs = false;
//        $PAGE->requires->js(new moodle_url('/mod/quiz/accessrule/proctoringlms/amd/src/myscript.js'), 'init');
        $PAGE->requires->js_call_amd('quizaccess_proctoringlms/proctoringlms', 'init', array());
//        $PAGE->requires->js_call_amd('quizaccess_proctoringlms/proctoring', 'init', array());
        $messages = [get_string('proctoringheader', 'quizaccess_proctoringlms')];

        $messages[] = $this->get_download_config_button();

//        return $messages;
        return get_string('proctoringheader_set', 'quizaccess_proctoringlms');
//        return 'Proctoring Enable';
    }
//    /**
//     * This function extends the settings navigation block for the site.
//     *
//     * It is safe to rely on PAGE here as we will only ever be within the module
//     * context when this is called
//     *
//     * @param settings_navigation $settings
//     * @param navigation_node $quiznode
//     * @param context $context
//     * @return void
//     */
//    function quizaccess_proctoringlms_extend_settings_navigation($settings, $context) {
//        global $PAGE, $CFG;
////
//        // Require {@link questionlib.php}
//        // Included here as we only ever want to include this file if we really need to.
////        require_once($CFG->libdir . '/questionlib.php');
////
//        // We want to add these new nodes after the Edit settings node, and before the
//        // Locally assigned roles node. Of course, both of those are controlled by capabilities.
////        $keys = $quiznode->get_children_key_list();
////        $beforekey = null;
////        $i = array_search('modedit', $keys);
////        if ($i === false and array_key_exists(0, $keys)) {
////            $beforekey = $keys[0];
////        } else if (array_key_exists($i + 1, $keys)) {
////            $beforekey = $keys[$i + 1];
////        }
////
////
////        if (has_capability('mod/quiz:manage', $PAGE->cm->context)) {
////            $node = navigation_node::create('kkkk',
////                new moodle_url('/mod/quiz/edit.php', array('cmid'=>$PAGE->cm->id)),
////                navigation_node::TYPE_SETTING, null, 'mod_quiz_edit',
////                new pix_icon('t/edit', ''));
////            $quiznode->add_node($node, $beforekey);
////        }
//        // Setup navigation for Admin metadata
//
//
//        global $PAGE;
////        if (!has_capability('moodle/course:manageactivities', $context)) {
////            return;
////        }
//        //echo "<pre>"; var_dump($PAGE->cm->instance); echo "</pre>";
//        if ($PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->cm->modname === 'forum') {
//            $pdflink = navigation_node::create('kkkkkkk');
//            $pdflink->key = 'forum2pdf';
//            $pdflink->action = new moodle_url('/local/forum2pdf/export.php', array('f' => $PAGE->cm->instance));
//            $modulesettings = $nav->get('modulesettings');
//            $modulesettings->add_node($pdflink);
//        }
//    }
    /**
     * Sets up the attempt (review or summary) page with any special extra
     * properties required by this rule.
     *
     * @param moodle_page $page the page object to initialise.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function setup_attempt_page($page) {
        $cmid = optional_param('cmid', '', PARAM_INT);
        $attempt = optional_param('attempt', '', PARAM_INT);

        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_popup_notification_allowed(false); // Prevent message notifications.
        $page->set_heading($page->title);

        global $DB, $COURSE, $USER, $CFG;
        if ($cmid) {
            $contextquiz = $DB->get_record('course_modules', array('id' => $cmid));

            $record = new stdClass();
            $record->courseid = $COURSE->id;
            $record->quizid = $contextquiz->id;
            $record->userid = $USER->id;
            $record->webcampicture = '';
            $record->status = $attempt;
            $record->timemodified = time();
            $record->id = $DB->insert_record('quizaccess_proctoringlmslogs', $record, true);
//            $page->requires->js_call_amd('quizaccess_proctoringlms/proctoringlms', 'setup', array($record));
//            $page->requires->js(new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/proctoringlms/amd/src/adapter-latest.js'));
//            $page->requires->js(new moodle_url('https://webrtc.github.io/adapter/adapter-latest.js'));
//            $page->requires->js('https://webrtc.github.io/adapter/adapter-latest.js');
//            $page->requires->js(new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/proctoringlms/amd/src/main.js'));
//            $page->requires->css('./styles.css');
//            $page->requires->js_call_amd('quizaccess_proctoringlms/adapter-latest');

            $page->requires->js_call_amd('quizaccess_proctoringlms/proctoringlms', 'setup', array($record));
        }
    }
    /**
     * Get a button to view the Proctoring report.
     *
     *
     * @return string A link to view report
     * @throws coding_exception
     */
    private function get_download_config_button() : string {
        global $OUTPUT, $USER;

        $context = context_module::instance($this->quiz->cmid, MUST_EXIST);
        if (has_capability('quizaccess/proctoringlms:viewreport', $context, $USER->id)) {
            $httplink = \quizaccess_proctoringlms\link_generator::get_link($this->quiz->course, $this->quiz->cmid, false, is_https());

            return $OUTPUT->single_button($httplink, get_string('picturesreport', 'quizaccess_proctoringlms'), 'get');
        } else {
            return '';
        }
    }

}
