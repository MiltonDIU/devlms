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
 * Extrarnal for the quizaccess_proctoringlms plugin.
 *
 * @package    quizaccess_proctoringlms
 * @author     Kamrul Islam
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

/**
 * External class.
 *
 * @copyright  2020 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_proctoringlms_external extends external_api
{

    /**
     * Set the cam shots parameters.
     *
     * @return external_function_parameters
     */
    public static function get_camshots_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'camshot course id'),
                'quizid' => new external_value(PARAM_INT, 'camshot quiz id'),
                'userid' => new external_value(PARAM_INT, 'camshot user id')
            )
        );
    }

    /**
     * Get the cam shots as service.
     *
     * @param mixed $courseid
     * @param mixed $quizid
     * @param mixed $userid
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_camshots($courseid, $quizid, $userid) {
        global $DB;

        // Validate the params.
        self::validate_parameters(
            self::get_camshots_parameters(),
            array(
                'courseid' => $courseid,
                'quizid' => $quizid,
                'userid' => $userid
            )
        );

        $warnings = array();
        if ($quizid) {
            $camshots = $DB->get_records('quizaccess_proctoringlmslogs',
            array('courseid' => $courseid, 'quizid' => $quizid, 'userid' => $userid), 'id DESC');
        } else {
            $camshots = $DB->get_records('quizaccess_proctoringlmslogs',
            array('courseid' => $courseid, 'userid' => $userid), 'id DESC');
        }

        $returnedcamhosts = array();

        foreach ($camshots as $camshot) {
            if ($camshot->webcampicture !== '') {
                $returnedcamhosts[] = array(
                    'courseid' => $camshot->courseid,
                    'quizid' => $camshot->quizid,
                    'userid' => $camshot->userid,
                    'webcampicture' => $camshot->webcampicture,
                    'timemodified' => $camshot->timemodified,
                );

            }
        }

        $result = array();
        $result['camshots'] = $returnedcamhosts;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Cam shot return parameters.
     *
     * @return external_single_structure
     */

    public static function get_camshots_returns() {
        return new external_single_structure(
            array(
                'camshots' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_NOTAGS, 'camshot course id'),
                            'quizid' => new external_value(PARAM_NOTAGS, 'camshot quiz id'),
                            'userid' => new external_value(PARAM_NOTAGS, 'camshot user id'),
                            'webcampicture' => new external_value(PARAM_RAW, 'camshot webcam photo'),
                            'timemodified' => new external_value(PARAM_NOTAGS, 'camshot time modified'),
                        )
                    ),
                    'list of camshots'
                ),
                'warnings' => new external_warnings()
            )
        );
    }


    /**
     * Store parameters.
     *
     * @return external_function_parameters
     */
    public static function send_camshot_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'screenshotid' => new external_value(PARAM_INT, 'screenshot id'),
                'quizid' => new external_value(PARAM_INT, 'screenshot quiz id'),
                'webcampicture' => new external_value(PARAM_RAW, 'webcam photo'),
            )
        );
    }

    /**
     * Store parameters.
     *
     * @return external_function_parameters
     */
    public static function send_scheen_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'screenshotid' => new external_value(PARAM_INT, 'screenshot id'),
                'quizid' => new external_value(PARAM_INT, 'screenshot quiz id'),
                'webcampicture' => new external_value(PARAM_RAW, 'webcam photo'),
            )
        );
    }
    /**
     * Store parameters.
     *
     * @return external_function_parameters
     */
    public static function send_rightworning_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'screenshotid' => new external_value(PARAM_INT, 'screenshot id'),
                'quizid' => new external_value(PARAM_INT, 'screenshot quiz id'),
                'webcampicture' => new external_value(PARAM_RAW, 'webcam photo'),
            )
        );
    }

    /**
     * Store the Cam shots in Moodle subsystems and insert in log table
     *
     * @param mixed $courseid
     * @param mixed $screenshotid
     * @param mixed $quizid Quizid OR cmid
     * @param mixed $webcampicture
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws stored_file_creation_exception
     */
    public static function send_camshot($courseid, $screenshotid, $quizid, $webcampicture) {
        global $DB, $USER;

        // Validate the params.
        self::validate_parameters(
            self::send_camshot_parameters(),
            array(
                'courseid' => $courseid,
                'screenshotid' => $screenshotid,
                'quizid' => $quizid,
                'webcampicture' => $webcampicture
            )
        );
        $warnings = array();

        $record = new stdClass();
        $record->filearea = 'picture';
        $record->component = 'quizaccess_proctoringlms';
        $record->filepath = '';
        $record->itemid   = $screenshotid;
        $record->license  = '';
        $record->author   = '';

        $context = context_module::instance($quizid);
        $fs = get_file_storage();
        $record->filepath = file_correct_filepath($record->filepath);

        // For base64 to file.
        $data = $webcampicture;
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $filename = 'webcam-' .$screenshotid . '-'. $USER->id . '-' . $courseid . '-' . time() . rand(1, 1000) . '.png';

        $record->courseid = $courseid;
        $record->filename = $filename;
        $record->contextid = $context->id;
        $record->userid    = $USER->id;

        $fs->create_file_from_string($record, $data);

        $url = moodle_url::make_pluginfile_url(
            $context->id,
            $record->component,
            $record->filearea,
            $record->itemid,
            $record->filepath,
            $record->filename,
            false
        );

        $camshot = $DB->get_record('quizaccess_proctoringlmslogs', array('id' => $screenshotid));

        if(!$camshot){
            $set_data = $screenshotid++;
        }else{
            $set_data = $camshot->status;
        }

        $record = new stdClass();
        $record->courseid = $courseid;
        $record->quizid = $quizid;
        $record->userid = $USER->id;
        $record->webcampicture = "{$url}";
        $record->status = $set_data;
        $record->timemodified = time();
        $screenshotid = $DB->insert_record('quizaccess_proctoringlmslogs', $record, true);

        $result = array();
        $result['screenshotid'] = $screenshotid;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Store the Cam shots in Moodle subsystems and insert in log table
     *
     * @param mixed $courseid
     * @param mixed $screenshotid
     * @param mixed $quizid Quizid OR cmid
     * @param mixed $webcampicture
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws stored_file_creation_exception
     */
    public static function send_scheen($courseid, $screenshotid, $quizid, $webcampicture) {
        global $DB, $USER;

        // Validate the params.
        self::validate_parameters(
            self::send_scheen_parameters(),
            array(
                'courseid' => $courseid,
                'screenshotid' => $screenshotid,
                'quizid' => $quizid,
                'webcampicture' => $webcampicture
            )
        );


        $warnings = array();

        $record = new stdClass();
        $record->filearea = 'picture';
        $record->component = 'quizaccess_proctoringlms';
        $record->filepath = '';
        $record->itemid   = $screenshotid;
        $record->license  = '';
        $record->author   = '';

        $context = context_module::instance($quizid);
        $fs = get_file_storage();
        $record->filepath = file_correct_filepath($record->filepath);

        // For base64 to file.
        $data = $webcampicture;
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $filename = 'scheen-' .$screenshotid . '-'. $USER->id . '-' . $courseid . '-' . time() . rand(1, 1000) . '.png';

        $record->courseid = $courseid;
        $record->filename = $filename;
        $record->contextid = $context->id;
        $record->userid    = $USER->id;

        $fs->create_file_from_string($record, $data);

        $url = moodle_url::make_pluginfile_url(
            $context->id,
            $record->component,
            $record->filearea,
            $record->itemid,
            $record->filepath,
            $record->filename,
            false
        );

        $camshot = $DB->get_record('quizaccess_proctoringscheen', array('id' => $screenshotid));
        if(!$camshot){
            $set_data = $screenshotid++;
        }else{
            $set_data = $camshot->status;
        }
        $record = new stdClass();
        $record->courseid = $courseid;
        $record->quizid = $quizid;
        $record->userid = $USER->id;
        $record->scheenpicture = "{$url}";
        $record->status = $set_data;
        $record->timemodified = time();
        $screenshotid = $DB->insert_record('quizaccess_proctoringscheen', $record, true);

        $result = array();
        $result['screenshotid'] = $screenshotid;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Store the Cam shots in Moodle subsystems and insert in log table
     *
     * @param mixed $courseid
     * @param mixed $screenshotid
     * @param mixed $quizid Quizid OR cmid
     * @param mixed $webcampicture
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws stored_file_creation_exception
     */

    public static function send_rightworning($courseid, $screenshotid, $quizid, $webcampicture) {
        global $DB, $USER;

//        $webcampicture = "kou";

        // Validate the params.
//        self::validate_parameters(
//            self::send_rightworning_parameters(),
//            array(
//                'courseid' => $courseid,
//                'screenshotid' => $screenshotid,
//                'quizid' => $quizid,
//                'webcampicture' => $webcampicture
//            )
//        );
        $warnings = array();

        $record = new stdClass();
        $record->filearea = 'picture';
        $record->component = 'quizaccess_proctoringlms';
        $record->filepath = '';
        $record->itemid   = $screenshotid;
        $record->license  = '';
        $record->author   = '';

//        $context = context_module::instance($quizid);
//        $fs = get_file_storage();
//        $record->filepath = file_correct_filepath($record->filepath);

        // For base64 to file.
//        $data = $webcampicture;
//        list($type, $data) = explode(';', $data);
//        list(, $data)      = explode(',', $data);
//        $data = base64_decode($data);
//        $filename = 'webcam-' .$screenshotid . '-'. $USER->id . '-' . $courseid . '-' . time() . rand(1, 1000) . '.png';

//        $record->courseid = $courseid;
//        $record->filename = $filename;
//        $record->contextid = $context->id;
//        $record->userid    = $USER->id;
//
//        $fs->create_file_from_string($record, $data);
//
//        $url = moodle_url::make_pluginfile_url(
//            $context->id,
//            $record->component,
//            $record->filearea,
//            $record->itemid,
//            $record->filepath,
//            $record->filename,
//            false
//        );

        $p_status = 1;
        $conditions_p = array(
            'courseid' => $courseid,
            'quizid' => $quizid,
            'userid' => $USER->id,
            'clickleval' => $webcampicture,
        );
//        $countdata = $DB->get_record('quizaccess_proctoringright', $conditions_p);

//        $countdata = $DB->get_record('quizaccess_proctoringright', array($DB->sql_compare_text('clickleval') => 'New tap'));
//        $sql = 'SELECT dc.id AS contentid, ' . 'dr.id AS recordid, ' . 'dc.content AS content, ' . 'd.id AS dataid ' . 'FROM ' . $CFG->prefix . 'data d, ' . $CFG->prefix . 'data_fields df, ' . $CFG->prefix . 'data_records dr, ' . $CFG->prefix . 'data_content dc ' . "WHERE (d.course = '{$courseid}' or d.course = '" . SITEID . "')" . 'AND d.id = df.dataid ' . 'AND df.id = dc.fieldid ' . 'AND d.id = dr.dataid ' . 'AND dr.id = dc.recordid ' . "AND df.type = 'text' " . "AND " . sql_compare_text('df.param1', 1) . " = '1'";
//        if (!($datacontents = get_records_sql($sql))) {
//            return $text;
//        }
//        $sql = "select * from {quizaccess_proctoringright} where 'courseid' = " . $courseid . " AND 'quizid' = " . $quizid . " AND 'userid' = " . $USER->id . " AND " . $DB->sql_compare_text('clickleval', 255) . " = 'New tap' ";
//        $countdata = $DB->get_record_sql($sql);
        $countdata = $DB->get_record_sql('SELECT * 
                FROM {quizaccess_proctoringright}
                WHERE courseid = :courseid 
                AND quizid = :quizid 
                AND userid = :userid 
                AND ' . $DB->sql_compare_text('clickleval', 100) . ' = ' . $DB->sql_compare_text(':clickleval', 100),
            $conditions_p, IGNORE_MISSING);
//        print_r($countdata);
        if($countdata){
            $p_countdata = $countdata->countdata + 1;

//            $record_up              = new stdClass();
            $countdata->countdata   = $p_countdata;
            $DB->update_record('quizaccess_proctoringright', $countdata);
        }else {
            $p_countdata = 1;

            $record = new stdClass();
            $record->courseid = $courseid;
            $record->quizid = $quizid;
            $record->userid = $USER->id;
            $record->clickleval = $webcampicture;
            $record->countdata = $p_countdata;
            $record->status = $p_status;
            $record->timemodified = time();
            $screenshotid = $DB->insert_record('quizaccess_proctoringright', $record, true);
        }

        $result = array();
        $result['screenshotid'] = $screenshotid;
        $result['warnings'] = $warnings;
        return $result;
    }


    /**
     * Cam shots return parameters.
     *
     * @return external_single_structure
     */
    public static function send_camshot_returns() {
        return new external_single_structure(
            array(
                'screenshotid' => new external_value(PARAM_INT, 'screenshot sent id'),
                'warnings' => new external_warnings()
            )
        );
    }
    /**
     * Cam shots return parameters.
     *
     * @return external_single_structure
     */
    public static function send_rightworning_returns() {
        return new external_single_structure(
            array(
                'screenshotid' => new external_value(PARAM_INT, 'screenshot sent id'),
                'warnings' => new external_warnings()
            )
        );
    }
    /**
     * Cam shots return parameters.
     *
     * @return external_single_structure
     */
    public static function send_scheen_returns() {
        return new external_single_structure(
            array(
                'screenshotid' => new external_value(PARAM_INT, 'screenshot sent id'),
                'warnings' => new external_warnings()
            )
        );
    }

}
