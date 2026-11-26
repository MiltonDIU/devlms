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
 * Get course stats service
 *
 * @package   theme_remui
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_remui\external;

defined('MOODLE_INTERNAL') || die;

use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

require_once($CFG->libdir . '/completionlib.php');

/**
 * Get course stats trait
 * @copyright (c) 2022 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait enrol_get_course_instructors {
    /**
     * Describes the parameters for enrol_get_course_instructors
     * @return external_function_parameters
     */
    public static function enrol_get_course_instructors_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Course Id'),
             ]
        );
    }

    /**
     * Save order of sections in array of configuration format
     * @param  int $courseid Course id
     * @return boolean       true
     */
    public static function enrol_get_course_instructors($courseid) {
        global $PAGE, $OUTPUT, $CFG, $USER, $DB;

        $params = self::validate_parameters(
            self::enrol_get_course_instructors_parameters(),
            ['courseid' => $courseid]
        );
        $courseid = $params['courseid'];
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($courseid);

        // Validate system context to ensure the user is authenticated.
        // We cannot validate the course context directly because this service
        // is called from the enrolment page where unenrolled users legitimately
        // view course contact information before enrolling.
        self::validate_context(\context_system::instance());

        // Enforce the same access rules as /enrol/index.php.
        // This allows unenrolled users and guests to view instructors on the
        // enrolment page while protecting hidden courses.
        if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $context)) {
            return json_encode(["instructors" => []]);
        }

        if (!\core_course_category::can_view_course_info($course) && !is_enrolled($context, $USER, '', true)) {
            return json_encode(["instructors" => []]);
        }

        $courseelement = new \core_course_list_element($course);
        $contacts = $courseelement->get_course_contacts();

        $instructors = [];


        $temparray = explode(',', $CFG->hiddenuserfields);
        $hiddenfields = [];
        foreach ($temparray as $value) {
            $hiddenfields[$value] = $value;
        }

        foreach ($contacts as $contact) {
            $teacher = $contact['user'];

            // Ensure full user record for privacy-sensitive fields.
            if (!isset($teacher->maildisplay) || !isset($teacher->description)) {
                $teacher = $DB->get_record('user', ['id' => $teacher->id], '*', MUST_EXIST);
            }

            $usercourses = enrol_get_users_courses($teacher->id, false, null, null);
            $totalcourses = 0;
            $isadmin = is_siteadmin($USER->id);
            foreach ($usercourses as $usercourse) {
                // Skip hidden courses for non-admins.
                if (!$usercourse->visible && !$isadmin) {
                    continue;
                }
                $tempcontext = \context_course::instance($usercourse->id);
                if (is_enrolled($tempcontext, $teacher->id, 'mod/folder:managefiles', false)) {
                    $totalcourses++;
                }
            }
            $instructor = [];
            $instructor['id'] = $teacher->id;
            $instructor['fullname'] = fullname($teacher, true);
            $instructor['avatar'] = $OUTPUT->user_picture($teacher, ['size' => 116]);
            // Removed it due to the issues with moodle guidelines. It is not recommended to show the total students count for a teacher.
            $instructor['totalstudents'] = 0;
            $instructor['totalcourses'] = $totalcourses;
            $instructor['profileurl'] = $CFG->wwwroot . '/user/profile.php?id=' . $teacher->id;
            // Apply core email visibility rules.
            $canviewhidden = has_capability('moodle/user:viewhiddendetails', $context);
            $instructor['email'] = false;
            if (
                $canviewhidden ||
                    (!isset($hiddenfields['email']) &&
                        ($teacher->maildisplay == 1 ||
                            ($teacher->maildisplay == 2 && enrol_sharing_course($USER->id, $teacher->id))))
            ) {
                $instructor['email'] = $teacher->email;
            }
            $description = file_rewrite_pluginfile_urls(
                $teacher->description,
                'pluginfile.php',
                $context->id,
                'user',
                'profile',
                $teacher->id
            );
            $instructor['description'] = format_text($description, FORMAT_HTML);
            if (isset($hiddenfields['description']) && !$canviewhidden) {
                $instructor['description'] = false;
            }

            $instructor['allowedmessaging'] = false;

            $instructors[] = $instructor;
        }

        return json_encode(["instructors" => $instructors]);
    }

    /**
     * Describes the enrol_get_course_instructors return value
     * @return external_value
     */
    public static function enrol_get_course_instructors_returns() {
        return new external_value(PARAM_RAW, 'course instructors data');
    }
}
