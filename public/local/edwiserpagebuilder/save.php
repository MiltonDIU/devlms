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
 * Save handler for VvvebJs page builder.
 *
 * Copyright 2017 Ziadin Givan
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * https://github.com/givanz/VvvebJs
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();
require_sesskey();
require_capability('local/edwiserpagebuilder:epb_can_manage_page', context_system::instance());

// 2 Megabytes max html file size.
define('LOCAL_EDWISERPAGEBUILDER_MAX_FILE_LIMIT', 1024 * 1024 * 2);
// Check if saved html contains php tag and don't save if not allowed.
define('LOCAL_EDWISERPAGEBUILDER_ALLOW_PHP', false);
// Load urls only from allowed websites for oembed.
define('LOCAL_EDWISERPAGEBUILDER_ALLOWED_OEMBED_DOMAINS', [
    'https://www.youtube.com/',
    'https://www.vimeo.com/',
    'https://www.x.com/',
    'https://x.com/',
    'https://publish.twitter.com/',
    'https://www.twitter.com/',
    'https://www.reddit.com/',
]);

/**
 * Sanitize a file path and restrict to allowed extension.
 *
 * @param string $file The file path to sanitize.
 * @param string $allowedextension The allowed file extension.
 * @return string The sanitized file path.
 */
function local_edwiserpagebuilder_sanitize_file_path($file, $allowedextension = 'html') {
    $basename = basename($file);
    $disallow = ['.htaccess', 'passwd'];
    if (in_array($basename, $disallow)) {
        local_edwiserpagebuilder_show_error(get_string('error_filenamenotallowed', 'local_edwiserpagebuilder'));
        return '';
    }

    // Sanitize, remove double dot and remove get parameters if any.
    $file = preg_replace('@\?.*$@', '', preg_replace('@\.{2,}@', '', preg_replace('@[^\/\\a-zA-Z0-9\-\._]@', '', $file)));

    if (!$file) {
        return '';
    }

    // Allow only specified extension.
    if ($allowedextension) {
        $file = preg_replace('/\.[^.]+$/', '', $file) . ".$allowedextension";
    }
    return $file;
}

/**
 * Convert a relative file path to a Moodle File API filepath and filename.
 *
 * @param string $file Relative file path.
 * @param string $defaultext Default extension to enforce.
 * @return array [filepath, filename]
 */
function local_edwiserpagebuilder_get_file_path_parts($file, $defaultext = 'html') {
    $file = local_edwiserpagebuilder_sanitize_file_path($file, $defaultext);
    if (!$file) {
        return ['', ''];
    }
    $dir = dirname($file);
    if ($dir === '.' || $dir === '/' || $dir === '\\') {
        $filepath = '/';
    } else {
        $filepath = '/' . trim(str_replace('\\', '/', $dir), '/') . '/';
    }
    $filename = basename($file);
    return [$filepath, $filename];
}

/**
 * Show an error and terminate execution.
 *
 * @param string $error The error message.
 */
function local_edwiserpagebuilder_show_error($error) {
    http_response_code(500);
    die($error);
}

/**
 * Check if a URL is from an allowed oembed domain.
 *
 * @param string $url The URL to validate.
 * @return bool True if the URL is allowed.
 */
function local_edwiserpagebuilder_valid_oembed_url($url) {
    foreach (LOCAL_EDWISERPAGEBUILDER_ALLOWED_OEMBED_DOMAINS as $domain) {
        if (strpos($url, $domain) === 0) {
            return true;
        }
    }

    return false;
}

$html   = '';
$file   = '';
$action = optional_param('action', '', PARAM_ALPHA);

$starttemplateurl = optional_param('startTemplateUrl', '', PARAM_PATH);
if ($starttemplateurl !== '') {
    $starttemplateurl = local_edwiserpagebuilder_sanitize_file_path($starttemplateurl);
    $html = '';
    if ($starttemplateurl) {
        // Try loading from File API first (user content).
        $context = context_system::instance();
        $fs = get_file_storage();
        [$filepath, $filename] = local_edwiserpagebuilder_get_file_path_parts($starttemplateurl);
        $fileobj = $fs->get_file($context->id, 'local_edwiserpagebuilder', 'content', 0, $filepath, $filename);
        if ($fileobj) {
            $html = $fileobj->get_content();
        } else {
            // Fall back to demo templates on disk.
            $diskpath = __DIR__ . DIRECTORY_SEPARATOR . $starttemplateurl;
            if (file_exists($diskpath)) {
                $html = file_get_contents($diskpath);
            }
        }
    }
} else {
    $rawhtml = optional_param('html', '', PARAM_RAW);
    if ($rawhtml !== '') {
        $html = substr($rawhtml, 0, LOCAL_EDWISERPAGEBUILDER_MAX_FILE_LIMIT);
        if (!LOCAL_EDWISERPAGEBUILDER_ALLOW_PHP) {
            if (preg_match('@<\?php|<\? |\<?=|<\s*script\s*language\s*=\s*"\s*php\s*"\s*>@', $html)) {
                local_edwiserpagebuilder_show_error(get_string('error_phpnotallowed', 'local_edwiserpagebuilder'));
            }
        }
    }
}

$postfile = optional_param('file', '', PARAM_PATH);
if ($postfile !== '') {
    $file = local_edwiserpagebuilder_sanitize_file_path($postfile);
}

$context = context_system::instance();
$fs = get_file_storage();

if ($action) {
    // File manager actions, delete and rename.
    switch ($action) {
        case 'rename':
            $rawnewfile = optional_param('newfile', '', PARAM_PATH);
            $newfile = local_edwiserpagebuilder_sanitize_file_path($rawnewfile);
            if ($file && $newfile) {
                [$oldfilepath, $oldfilename] = local_edwiserpagebuilder_get_file_path_parts($file);
                [$newfilepath, $newfilename] = local_edwiserpagebuilder_get_file_path_parts($newfile);

                $oldfileobj = $fs->get_file($context->id, 'local_edwiserpagebuilder', 'content', 0, $oldfilepath, $oldfilename);
                if ($oldfileobj) {
                    $content = $oldfileobj->get_content();
                    $oldfileobj->delete();

                    $filerecord = new stdClass();
                    $filerecord->contextid = $context->id;
                    $filerecord->component = 'local_edwiserpagebuilder';
                    $filerecord->filearea  = 'content';
                    $filerecord->itemid    = 0;
                    $filerecord->filepath  = $newfilepath;
                    $filerecord->filename  = $newfilename;
                    $filerecord->userid    = $USER->id;

                    $fs->create_file_from_string($filerecord, $content);
                    echo get_string(
                        'filerenamedto',
                        'local_edwiserpagebuilder',
                        (object)['oldfile' => $file, 'newfile' => $newfile]
                    );
                } else {
                    local_edwiserpagebuilder_show_error(get_string(
                        'error_filerenamefailed',
                        'local_edwiserpagebuilder',
                        (object)['oldfile' => $file, 'newfile' => $newfile]
                    ));
                }
            }
            break;
        case 'delete':
            if ($file) {
                [$filepath, $filename] = local_edwiserpagebuilder_get_file_path_parts($file);
                $fileobj = $fs->get_file($context->id, 'local_edwiserpagebuilder', 'content', 0, $filepath, $filename);
                if ($fileobj && $fileobj->delete()) {
                    echo get_string('success_filedeleted', 'local_edwiserpagebuilder');
                } else {
                    local_edwiserpagebuilder_show_error(get_string('error_filedeletefailed', 'local_edwiserpagebuilder', $file));
                }
            }
            break;
        case 'saveReusable':
            // Block or section.
            $type = optional_param('type', '', PARAM_ALPHA);
            $name = optional_param('name', '', PARAM_ALPHANUMEXT);
            $html = optional_param('html', '', PARAM_RAW);

            if ($type && $name && $html) {
                $filepath = '/' . $type . '/';
                $filename = $name . '.html';

                $existing = $fs->get_file($context->id, 'local_edwiserpagebuilder', 'reusable', 0, $filepath, $filename);
                if ($existing) {
                    $existing->delete();
                }

                $filerecord = new stdClass();
                $filerecord->contextid = $context->id;
                $filerecord->component = 'local_edwiserpagebuilder';
                $filerecord->filearea  = 'reusable';
                $filerecord->itemid    = 0;
                $filerecord->filepath  = $filepath;
                $filerecord->filename  = $filename;
                $filerecord->userid    = $USER->id;

                if ($fs->create_file_from_string($filerecord, $html)) {
                    echo get_string('success_filesaved', 'local_edwiserpagebuilder');
                } else {
                    local_edwiserpagebuilder_show_error(
                        get_string('error_filesavefailed', 'local_edwiserpagebuilder', $filepath . $filename)
                    );
                }
            } else {
                local_edwiserpagebuilder_show_error(get_string('error_missingreusabledata', 'local_edwiserpagebuilder'));
            }
            break;
        case 'oembedProxy':
            $url = optional_param('url', '', PARAM_URL);
            if (local_edwiserpagebuilder_valid_oembed_url($url)) {
                $curl = new \curl();
                $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Moodle';
                $options = [
                    'CURLOPT_USERAGENT' => $useragent,
                ];
                $response = $curl->get($url, [], $options);
                if ($response === false || $curl->get_errno()) {
                    local_edwiserpagebuilder_show_error(get_string('error_invalidurl', 'local_edwiserpagebuilder'));
                }
                header('Content-Type: application/json');
                echo $response;
            } else {
                local_edwiserpagebuilder_show_error(get_string('error_invalidurl', 'local_edwiserpagebuilder'));
            }
            break;
        default:
            local_edwiserpagebuilder_show_error(get_string('error_invalidaction', 'local_edwiserpagebuilder', $action));
    }
} else {
    // Save page.
    if ($html) {
        if ($file) {
            [$filepath, $filename] = local_edwiserpagebuilder_get_file_path_parts($file);

            $existing = $fs->get_file($context->id, 'local_edwiserpagebuilder', 'content', 0, $filepath, $filename);
            if ($existing) {
                $existing->delete();
            }

            $filerecord = new stdClass();
            $filerecord->contextid = $context->id;
            $filerecord->component = 'local_edwiserpagebuilder';
            $filerecord->filearea  = 'content';
            $filerecord->itemid    = 0;
            $filerecord->filepath  = $filepath;
            $filerecord->filename  = $filename;
            $filerecord->userid    = $USER->id;

            if ($fs->create_file_from_string($filerecord, $html)) {
                echo get_string('success_filesaved', 'local_edwiserpagebuilder');
            } else {
                local_edwiserpagebuilder_show_error(
                    get_string('error_filesavefailed', 'local_edwiserpagebuilder', $filepath . $filename)
                );
            }
        } else {
            local_edwiserpagebuilder_show_error(get_string('error_filenameempty', 'local_edwiserpagebuilder'));
        }
    } else {
        local_edwiserpagebuilder_show_error(get_string('error_htmlempty', 'local_edwiserpagebuilder'));
    }
}
