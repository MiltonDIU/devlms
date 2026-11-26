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
 * Scan media folder for all files to display in media modal.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();
require_sesskey();
require_capability('local/edwiserpagebuilder:epb_can_view_page', context_system::instance());

$context = context_system::instance();
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'local_edwiserpagebuilder', 'media', 0, 'itemid', false);

/**
 * Build a nested file tree from flat file records.
 *
 * @param array $files Array of stored_file objects.
 * @return array Nested tree structure.
 */
function local_edwiserpagebuilder_build_file_tree($files) {
    $tree = [
        'name'  => '',
        'type'  => 'folder',
        'path'  => '',
        'items' => [],
    ];

    foreach ($files as $file) {
        if ($file->is_directory()) {
            continue;
        }

        $filepath = trim($file->get_filepath(), '/');
        $filename = $file->get_filename();
        $pathparts = $filepath ? explode('/', $filepath) : [];

        $current = &$tree['items'];
        $builtpath = '';

        foreach ($pathparts as $part) {
            $builtpath .= ($builtpath ? '/' : '') . $part;
            $found = false;
            foreach ($current as &$item) {
                if ($item['type'] === 'folder' && $item['name'] === $part) {
                    $current = &$item['items'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newfolder = [
                    'name'  => $part,
                    'type'  => 'folder',
                    'path'  => $builtpath,
                    'items' => [],
                ];
                $current[] = $newfolder;
                $current = &$newfolder['items'];
            }
        }

        $filepath = trim($file->get_filepath(), '/');
        $current[] = [
            'name' => $filename,
            'type' => 'file',
            'path' => ($filepath ? $filepath . '/' : '') . $filename,
            'size' => $file->get_filesize(),
        ];
    }

    return $tree;
}

$response = local_edwiserpagebuilder_build_file_tree($files);

// Output the directory listing as JSON.
header('Content-type: application/json');
echo json_encode($response);
