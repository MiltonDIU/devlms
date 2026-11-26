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
 * Block import and export handler class.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Gourav Govande
 */

namespace local_edwiserpagebuilder;

// @codingStandardsIgnoreLine
require_once($CFG->dirroot . '/config.php');

/**
 * This class handles block import and export operations.
 */
class block_import_export {
    /** @var int Maximum size of the raw import payload before base64 decode (50 MB). */
    private const MAX_PAYLOAD_SIZE = 50 * 1024 * 1024;

    /** @var int Maximum file size for external downloads (10 MB). */
    private const MAX_DOWNLOAD_SIZE = 10 * 1024 * 1024;

    /** @var int cURL timeout for external downloads (seconds). */
    private const DOWNLOAD_TIMEOUT = 30;

    /** @var int cURL connection timeout for external downloads (seconds). */
    private const DOWNLOAD_CONNECT_TIMEOUT = 10;
    /**
     * Perform the requested action.
     *
     * @param string $action Action name.
     * @param mixed  $config Configuration data.
     * @return mixed Action result.
     */
    public function perform_action($action, $config) {
        $functionname = "action_" . $action;

        // Check if the function exists before calling it.
        if (method_exists($this, $functionname)) {
            // Call the function dynamically.
            return call_user_func([$this, $functionname], $config);
        } else {
            // Handle the case when the function doesn't exist.
            return "Function $functionname does not exist.";
        }
    }

    /**
     * Export blocks data action handler.
     *
     * @param string $config JSON config string.
     * @return string Exported data.
     */
    public function action_export_blocks_data($config) {

        $config = json_decode($config, true);

        $blockid = $config['blockid'];

        return $this->export_block_data($blockid);
    }

    /**
     * Import blocks data action handler.
     *
     * @param string $config JSON config string.
     * @return mixed Import result.
     */
    public function action_import_blocks_data($config) {
        $config = json_decode($config, true);

        $blockid = $config['blockid'];

        $blockdata  = $config['blockdata'];

        return $this->import_block_data($blockid, $blockdata);
    }

    /**
     * Export block data by block id.
     *
     * @param int $blockid Block instance id.
     * @return string Base64 encoded export data.
     */
    public function export_block_data($blockid) {
        global $DB;

        // Fetch block data from the database.
        $block = $DB->get_record('block_instances', ['id' => $blockid], 'id, blockname, configdata');
        if (!$block || $block->blockname !== 'edwiseradvancedblock') {
            throw new Exception('Block not found.');
        }

        // Decode the config data.
        $config = unserialize(base64_decode($block->configdata));

        $mediadetails = $this->extract_media_details($config);

        $allassets = [];

        // Retrieve associated files.
        $context = \context_system::instance();

        $fs = get_file_storage();

        foreach ($mediadetails as $media) {
            $tempfile = [];

            $files = $fs->get_area_files($context->id, 'local_edwiserpagebuilder', 'media', $media['itemid'], 'itemid', false);

            foreach ($files as $file) {
                if ($file->get_filename() != '.') {
                    $fullfileurl = \moodle_url::make_pluginfile_url(
                        $context->id,
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                    )->__toString();
                    $tempfile = [
                        'filename' => $file->get_filename(),
                        'filesize' => $file->get_filesize(),
                        'mimetype' => $file->get_mimetype(),
                        'sourceurl' => $fullfileurl,
                    ];
                    break;
                }
            }
            $allassets[] = $tempfile;
        }

        // Prepare data for export.
        $data = [];
        $data['validator'] = true;
        $data['config'] = $config;
        $data['files'] = $allassets;

        $data = json_encode($data, JSON_PRETTY_PRINT);
        $data = base64_encode($data);

        return $data;
    }

    /**
     * Extract media details from block config data.
     *
     * @param \stdClass $configdata Block config data.
     * @return array Media details array.
     */
    public function extract_media_details($configdata) {
        // Initialize an empty array to store media details.
        $mediadetails = [];

        // Check if html content is present in the config data.
        if (isset($configdata->html['text'])) {
            $htmltext = $configdata->html['text'];

            // Decode the HTML entities to get proper URLs.
            $htmltext = html_entity_decode($htmltext);

            // Regular expression to find URLs containing local_edwiserpagebuilder media.
            preg_match_all('/\/local_edwiserpagebuilder\/media\/(\d+)\/([^"\/\?;]+)/', $htmltext, $matches);

            // Matches[1] contains item IDs and matches[2] contains filenames.
            if (isset($matches[1]) && isset($matches[2])) {
                foreach ($matches[1] as $index => $itemid) {
                    $filename = $matches[2][$index];

                    // Check if the file is an image or video based on its extension.
                    $fileextension = pathinfo($filename, PATHINFO_EXTENSION);
                    $filetype = '';

                    if (in_array(strtolower($fileextension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                        $filetype = 'image';
                    } else if (in_array(strtolower($fileextension), ['mp4', 'avi', 'mov', 'webm'])) {
                        $filetype = 'video';
                    } else {
                        $filetype = 'other';
                    }

                    // Store the result in the array.
                    $mediadetails[] = [
                        'itemid' => $itemid,
                        'filename' => $filename,
                        'type' => $filetype,
                    ];
                }
            }
        }

        return $mediadetails;
    }

    /**
     * Import block data from exported data.
     *
     * @param int    $blockid   Block instance id.
     * @param string $blockdata Base64 encoded block data.
     * @return array Status and message.
     */
    public function import_block_data($blockid, $blockdata) {
        global $DB;

        $response = [
            "status" => false,
            "message" => get_string("blockimportexportwarning", "local_edwiserpagebuilder"),
        ];

        // Fetch block data from the database.
        $block = $DB->get_record('block_instances', ['id' => $blockid]);
        if (!$block || $block->blockname !== 'edwiseradvancedblock') {
            return $response;
        }

        try {
            if (!is_string($blockdata)) {
                return $response;
            }
            // Reject unreasonably large payloads before memory-intensive base64 decode.
            if (strlen($blockdata) > self::MAX_PAYLOAD_SIZE) {
                return $response;
            }
            $blockdata = base64_decode($blockdata);
            $blockdata = json_decode($blockdata, true);

            if (!$blockdata) {
                return $response;
            }

            $data = $blockdata;

            if (!isset($data['config']) || !isset($data['files'])) {
                return $response;
            }
        } catch (TypeError $e) {
            return $response;
        }

        $config = $data['config'];
        $files = $data['files'];

        $context = \context_system::instance();
        $fs = get_file_storage();

        // Loop through each file in the JSON data and store them in the Moodle file storage.
        foreach ($files as $filedata) {
            // Validate required fields.
            // BACKWARD COMPATIBILITY: accept both new 'sourceurl' and old 'fullfilepath'.
            $sourceurl = !empty($filedata['sourceurl'])
                ? $filedata['sourceurl']
                : (!empty($filedata['fullfilepath']) ? $filedata['fullfilepath'] : '');
            if (empty($filedata['filename']) || empty($sourceurl)) {
                continue;
            }

            // Sanitize filename — never trust client input.
            $filename = clean_param($filedata['filename'], PARAM_FILE);
            if (empty($filename)) {
                continue;
            }

            // SECURITY: only allow expected image/video extensions.
            $allowedextensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'mp4', 'avi', 'mov', 'webm'];
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedextensions, true)) {
                continue;
            }

            // SECURITY: generate a server-side filename; keep only the validated extension.
            $filename = uniqid('epb_', true) . '.' . $extension;
            $filecontent = false;

            // SECURITY: First try a local file-system lookup (same-site import).
            // This avoids all HTTP requests and is the fastest, safest path.
            $localfile = $this->get_local_file_from_url($sourceurl);
            if ($localfile) {
                $filecontent = $localfile->get_content();
            } else {
                // SECURITY: For cross-site imports, strictly validate the URL
                // before making any outbound request.
                $validatedurl = $this->validate_external_url($sourceurl);
                if ($validatedurl) {
                    $expectedsize = !empty($filedata['filesize']) ? (int)$filedata['filesize'] : 0;
                    $filecontent = $this->safe_download_file($validatedurl, $expectedsize);
                }
            }

            if ($filecontent === false) {
                continue;
            }

            // SECURITY: Build the File API record server-side.
            // Do NOT copy component, filearea, itemid, filepath, or filename
            // from the imported payload.
            $filerecord = new \stdClass();
            $filerecord->contextid = $context->id;
            $filerecord->component = 'local_edwiserpagebuilder';
            $filerecord->filearea = 'media';
            $filerecord->itemid = file_get_unused_draft_itemid();
            $filerecord->filepath = '/';
            $filerecord->filename = $filename;

            $fs->create_file_from_string($filerecord, $filecontent);

            // Generate the full file URL for HTML replacement.
            $fullfilepath = \moodle_url::make_pluginfile_url(
                $filerecord->contextid,
                $filerecord->component,
                $filerecord->filearea,
                $filerecord->itemid,
                $filerecord->filepath,
                $filerecord->filename,
            )->__toString();

            // Replace the old URL in the config HTML with the new file URL.

            $config['html']['text'] = str_replace(
                $sourceurl,
                $fullfilepath,
                $config['html']['text']
            );
        }

        // Replace the URL in the config HTML with the new full filepath URL.
        $dataobj = new \stdClass();

        $dataobj->html = $config['html'];

        $dataobj->css = $config['css'];

        $dataobj->js = $config['js'];

        $instance = block_instance($block->blockname, $block);
        $instance->instance_config_save($dataobj, false);

        $response = [
            "status" => "success",
            "message" => get_string('blockimportsuccess', 'local_edwiserpagebuilder'),
        ];
        return $response;
    }

    /**
     * Attempt to look up a file directly in Moodle file storage from a pluginfile URL.
     * This avoids any HTTP requests and eliminates SSRF risk for same-site imports.
     *
     * @param string $url The full pluginfile URL.
     * @return \stored_file|false The stored file if found, false otherwise.
     */
    private function get_local_file_from_url($url) {
        global $CFG;

        // Must belong to this site.
        if (strpos($url, $CFG->wwwroot) !== 0) {
            return false;
        }

        $path = substr($url, strlen($CFG->wwwroot));

        // Parse standard slasharguments format.
        // /pluginfile.php/{contextid}/{component}/{filearea}/{itemid}/{filepath...}/{filename}.
        if (preg_match('#^/pluginfile\.php/(\d+)/([^/]+)/([^/]+)/(\d+)(.*)/([^/]+)$#', $path, $matches)) {
            $contextid = (int) $matches[1];
            $component = $matches[2];
            $filearea  = $matches[3];
            $itemid    = (int) $matches[4];
            $filepath  = $matches[5] . '/';
            $filename  = $matches[6];
        } else if (preg_match('#^/local/edwiserpagebuilder/media/(\d+)/([^/]+)$#', $path, $matches)) {
            // URL-rewritten format (e.g. Apache/Nginx rewrite of pluginfile.php).
            $contextid = \context_system::instance()->id;
            $component = 'local_edwiserpagebuilder';
            $filearea  = 'media';
            $itemid    = (int) $matches[1];
            $filepath  = '/';
            $filename  = $matches[2];
        } else {
            return false;
        }

        // Only allow this plugin's intended media area.
        if ($component !== 'local_edwiserpagebuilder' || $filearea !== 'media') {
            return false;
        }

        $fs = get_file_storage();
        return $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
    }

    /**
     * Strictly validate an external URL before fetching it.
     * Rejects private IPs, localhost, non-HTTPS (on HTTPS sites), and non-image/video URLs.
     *
     * @param string $url The URL to validate.
     * @return string|false The validated URL, or false if invalid.
     */
    private function validate_external_url($url) {
        global $CFG;

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        // Scheme must match the site's own scheme (https strongly preferred).
        $allowedscheme = (strpos($CFG->wwwroot, 'https://') === 0) ? 'https' : 'http';
        if ($parsed['scheme'] !== $allowedscheme) {
            return false;
        }

        // Reject localhost and loopback variants by name.
        $lowhost = strtolower($parsed['host']);
        $localhostnames = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        if (in_array($lowhost, $localhostnames, true)) {
            return false;
        }

        // Resolve hostname and reject private/reserved IP ranges.
        $ip = gethostbyname($parsed['host']);
        if ($ip === $parsed['host']) {
            // The gethostbyname call returned the hostname unchanged — it may already be an IP.
            $ip = $parsed['host'];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return $url;
    }

    /**
     * Safely download a file from an external URL with strict size and type limits.
     *
     * @param string $url The validated URL to download.
     * @param int    $expectedsize Optional expected file size for validation.
     * @return string|false The file content, or false on failure.
     */
    private function safe_download_file($url, $expectedsize = 0) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $options = [
            'CURLOPT_TIMEOUT'        => self::DOWNLOAD_TIMEOUT,
            'CURLOPT_CONNECTTIMEOUT' => self::DOWNLOAD_CONNECT_TIMEOUT,
            'CURLOPT_FOLLOWLOCATION' => false,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_MAXFILESIZE'    => self::MAX_DOWNLOAD_SIZE,
        ];

        $response = $curl->get($url, [], $options);
        $info     = $curl->get_info();
        $errno    = $curl->get_errno();
        $httpcode = !empty($info['http_code']) ? (int) $info['http_code'] : 0;
        $downloadedsize = !empty($info['size_download']) ? (int) $info['size_download'] : 0;
        $contenttype    = !empty($info['content_type']) ? $info['content_type'] : '';

        // Basic transport validation.
        if ($errno !== 0 || $response === false || $httpcode < 200 || $httpcode >= 300) {
            debugging(
                "Safe download failed: HTTP $httpcode, errno: $errno, URL: $url",
                DEBUG_DEVELOPER
            );
            return false;
        }

        // Size validation.
        if ($downloadedsize > self::MAX_DOWNLOAD_SIZE) {
            debugging(
                "Safe download rejected oversized file: $downloadedsize bytes, URL: $url",
                DEBUG_DEVELOPER
            );
            return false;
        }

        // Content-type validation — only images and videos.
        $allowedprefixes = ['image/', 'video/'];
        $validtype = false;
        foreach ($allowedprefixes as $prefix) {
            if (stripos($contenttype, $prefix) === 0) {
                $validtype = true;
                break;
            }
        }
        if (!$validtype) {
            debugging(
                "Safe download rejected invalid content type '$contenttype', URL: $url",
                DEBUG_DEVELOPER
            );
            return false;
        }

        // Optional expected-size validation (±10 % tolerance).
        if ($expectedsize > 0) {
            $tolerance = (int) ($expectedsize * 0.1);
            $minsize   = $expectedsize - $tolerance;
            $maxsize   = $expectedsize + $tolerance;
            if ($downloadedsize < $minsize || $downloadedsize > $maxsize) {
                debugging(
                    "Safe download size mismatch: expected ~$expectedsize, got $downloadedsize, URL: $url",
                    DEBUG_DEVELOPER
                );
                return false;
            }
        }

        return $response;
    }
}
