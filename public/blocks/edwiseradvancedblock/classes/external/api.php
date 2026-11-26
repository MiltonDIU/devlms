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
 * External API class for block_edwiseradvancedblock.
 *
 * @package   block_edwiseradvancedblock
 * @copyright (c) 2022 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Gourav Govande
 */

namespace block_edwiseradvancedblock\external;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . "/externallib.php");

// Backward-compatible aliases for renamed external API classes (MDL-76583).
// In Moodle 4.2+ these classes live in the core_external namespace.
// Creating aliases here prevents the autoloader from emitting deprecation
// debugging() messages every time a trait file references the old name.
$epbexternalaliases = [
    'external_api'                  => \core_external\external_api::class,
    'external_function_parameters'  => \core_external\external_function_parameters::class,
    'external_value'                => \core_external\external_value::class,
    'external_single_structure'     => \core_external\external_single_structure::class,
    'external_multiple_structure'   => \core_external\external_multiple_structure::class,
    'external_format_value'         => \core_external\external_format_value::class,
    'external_description'          => \core_external\external_description::class,
    'external_files'                => \core_external\external_files::class,
    'external_warnings'             => \core_external\external_warnings::class,
    'external_settings'             => \core_external\external_settings::class,
];
foreach ($epbexternalaliases as $oldname => $newname) {
    if (class_exists($newname) && !class_exists($oldname, false)) {
        class_alias($newname, $oldname, false);
    }
}

use external_api;

/**
 * Class api for block_edwiseradvancedblock external functions.
 */
class api extends external_api {
    use set_block_config;
}
