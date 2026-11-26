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
 * External functions for getting course list.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2022 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Abhishek Kushwah
 */
namespace local_edwiserpagebuilder\external;


use external_function_parameters;
use external_value;
use context_system;
use context_user;

/**
 * Trait for get course list external functions.
 */
trait get_courselist {
    /**
     * Describes the parameters for get_frontpage_section_courses_in_category
     * @return external_function_parameters
     */
    public static function get_courselist_parameters() {
        return new external_function_parameters(
            [
                'config' => new external_value(PARAM_RAW, 'courseids'),
                'search' => new external_value(PARAM_RAW, 'Search string', VALUE_DEFAULT, ''),
                'limit' => new external_value(PARAM_INT, 'Limit', VALUE_DEFAULT, 50),
                'selectedids' => new external_value(PARAM_RAW, 'Comma-separated selected course IDs', VALUE_DEFAULT, ''),
            ]
        );
    }

    /**
     * Get course list for the featured courses dropdown.
     *
     * This endpoint is restricted to logged-in users who can manage pages.
     * It respects Moodle course visibility, category visibility, and the
     * caller's capabilities before returning any course metadata.
     *
     * @param  string $config      Legacy config value.
     * @param  string $search      Search string for filtering courses.
     * @param  int    $limit       Maximum number of courses to return.
     * @param  string $selectedids Comma-separated list of selected course IDs.
     * @return string              JSON-encoded courses list.
     */
    public static function get_courselist($config, $search = '', $limit = 50, $selectedids = '') {
        global $PAGE, $DB;

        $context = \context_system::instance();
        $PAGE->set_context($context);
        self::validate_context($context);
        require_capability('local/edwiserpagebuilder:epb_can_manage_page', $context);

        // Enforce reasonable limits to prevent memory issues.
        $limit = min(max($limit, 1), 500);

        $selectedidsarr = [];
        if (!empty($selectedids)) {
            $selectedidsarr = array_map('intval', explode(',', $selectedids));
            $selectedidsarr = array_filter($selectedidsarr);
        }

        $search = trim($search);

        // Only show courses in categories the caller is allowed to see.
        $visiblecats = \core_course_category::make_categories_list();
        if (empty($visiblecats)) {
            return json_encode([]);
        }
        [$catsql, $catparams] = $DB->get_in_or_equal(array_keys($visiblecats), SQL_PARAMS_NAMED);

        // Admins and users with moodle/course:viewhiddencourses may see hidden courses.
        $showhidden = is_siteadmin() || has_capability('moodle/course:viewhiddencourses', $context);

        $courses = [];
        // Fetch selected courses first so they are always available.
        if (!empty($selectedidsarr)) {
            [$insql, $inparams] = $DB->get_in_or_equal($selectedidsarr, SQL_PARAMS_NAMED, 'sel');
            $where = "id $insql AND id != :siteid AND category $catsql";
            $params = array_merge($inparams, ['siteid' => SITEID], $catparams);
            if (!$showhidden) {
                $where .= ' AND visible = 1';
            }
            $courses = $DB->get_records_select('course', $where, $params, 'fullname', 'id, fullname', 0, $limit);
        }

        // Fetch additional courses up to the limit.
        $remaining = $limit - count($courses);
        if ($remaining > 0) {
            $params = ['siteid' => SITEID];
            $where = "id != :siteid AND category $catsql";
            $params = array_merge($params, $catparams);

            if (!empty($selectedidsarr)) {
                [$notinsql, $notinparams] = $DB->get_in_or_equal($selectedidsarr, SQL_PARAMS_NAMED, 'sel', false);
                $where .= " AND id $notinsql";
                $params = array_merge($params, $notinparams);
            }

            if ($search !== '') {
                $where .= ' AND ' . $DB->sql_like('fullname', ':search', false);
                $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
            }

            if (!$showhidden) {
                $where .= ' AND visible = 1';
            }

            $morecourses = $DB->get_records_select('course', $where, $params, 'fullname', 'id, fullname', 0, $remaining);
            $courses = $courses + $morecourses;
        }

        foreach ($courses as &$course) {
            $course->fullname = format_text($course->fullname, FORMAT_HTML);
        }

        return json_encode($courses);
    }

    /**
     * Describes the  get_courselist_returns  value
     * @return external_value
     */
    public static function get_courselist_returns() {
        return new external_value(PARAM_RAW, 'Courses list');
    }
}
