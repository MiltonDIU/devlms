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
 * Edwiser page builder license controller is necessary to activate, deactivate or renew license.
 *
 * @package   local_edwiserpagebuilder
 * @copyright Copyright (c) 2024 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Edwiser page builder license controller class
 */
class edwiserpagebuilder_license_controller {
    /**
     *
     * @var string Short Name for plugin.
     */
    private $pluginshortname = '';

    /**
     *
     * @var string Slug to be used in url and functions name
     */
    private $pluginslug = '';

    /**
     *
     * @var string stores the current plugin version
     */
    private $pluginversion = '';

    /**
     *
     * @var string Handles the plugin name
     */
    private $pluginname = '';

    /**
     *
     * @var string  Stores the URL of store. Retrieves updates from
     *              this store
     */
    private $storeurl = '';

    /**
     *
     * @var string  Name of the Author
     */
    private $authorname = '';

    /**
     * Response data
     * @var array
     */
    public static $responsedata;

    /**
     * Developer Note: This variable is used everywhere to check license information and verify the data.
     * Change the Name of this variable in this file wherever it appears and also remove this comment
     * After you are done with adding Licensing
     * @var array
     */
    public $wdmedwiserpagebuilderdata = [
        // Plugins short name appears on the License Menu Page.
        'plugin_short_name' => 'Edwiser Page Builder For Moodle',
        // Data in db. License is checked using two options viz edd_<slug>_license_key and edd_<slug>_license_status.
        'plugin_slug' => 'edwiser-page-builder-for-moodle',
        // This should be similar to Version tag mentioned in Plugin headers.
        'plugin_version' => '1.0.0',
        // Under this Name product should be created on WisdmLabs Site.
        'plugin_name' => 'Edwiser Page Builder For Moodle',
        // Url where program pings to check if update is available and license validity.
        'store_url' => 'https://edwiser.org/check-update',
        // Author Name.
        'author_name' => 'WisdmLabs',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->authorname       = $this->wdmedwiserpagebuilderdata['author_name'];
        $this->pluginname       = $this->wdmedwiserpagebuilderdata['plugin_name'];
        $this->pluginshortname = $this->wdmedwiserpagebuilderdata['plugin_short_name'];
        $this->pluginslug       = $this->wdmedwiserpagebuilderdata['plugin_slug'];
        $this->pluginversion    = $this->wdmedwiserpagebuilderdata['plugin_version'];
        $this->storeurl         = $this->wdmedwiserpagebuilderdata['store_url'];
    }

    /**
     * The function parses the response come from the edwiser.org
     * on activation and determines is status of the license key.
     *
     *
     * @param  object $licensedata the response retune by the activation request.
     * @return String               returns the license key status
     */
    public function status_update($licensedata) {
        global $DB;

        $status = "";
        if ((empty($licensedata->success)) && isset($licensedata->error) && ($licensedata->error == "expired")) {
            $status = 'expired';
        } else if ($licensedata->license == 'invalid' && isset($licensedata->error) && $licensedata->error == "disabled") {
            $status = 'disabled';
        } else if (
            $licensedata->license == 'invalid' && isset($licensedata->error)
            && $licensedata->error == "no_activations_left"
        ) {
            $status = 'no_activations_left';
        } else if ($licensedata->license == 'failed') {
            $status = 'failed';
             $GLOBALS['wdm_license_activation_failed'] = true;
        } else {
            $status = $licensedata->license;
        }

        // Delete previous license status.
        unset_config('edd_' . $this->pluginslug . '_license_status', 'local_edwiserpagebuilder');

        set_config('edd_' . $this->pluginslug . '_license_status', $status, 'local_edwiserpagebuilder');

        return $status;
    }

    /**
     * Check if there is no license data in response
     * @param  object $licensedata         License data
     * @param  int    $currentresponsecode Current response code from Edwiser site
     * @param  int    $validresponsecode   Valid response code
     * @return bool                        True if license is valid
     */
    public function check_if_no_data($licensedata, $currentresponsecode, $validresponsecode) {
        global $DB;

        if (
            $licensedata == null ||
            !in_array($currentresponsecode, $validresponsecode)
        ) {
            // @codingStandardsIgnoreLine
            $GLOBALS[ 'wdm_server_null_response' ] = true;

            // Delete previous record.
            try {
                unset_config('wdm_' . $this->pluginslug . '_license_trans', 'local_edwiserpagebuilder');
            } catch (dml_exception $e) {
                $rcd = 0;
                // Keep catch empty if no record found.
            }

            // Insert new license trans.
            set_config(
                'wdm_' . $this->pluginslug . '_license_trans',
                json_encode(['server_did_not_respond', time() + (60 * 60 * 24)]),
                'local_edwiserpagebuilder'
            );
            return false;
        }
        return true;
    }

    /**
     * Activate theme license
     */
    public function activate_license() {
        global $DB, $CFG;
        $licensekey = trim(optional_param('edd_' . $this->pluginslug . '_license_key', '', PARAM_TEXT));

        if ($licensekey) {
            // Delete existing license key record if exists.
            try {
                unset_config('edd_' . $this->pluginslug . '_license_key', 'local_edwiserpagebuilder');
            } catch (dml_exception $e) {
                // No record found, safe to ignore.
                // Handle exception based on context: CLI scripts need error output, web requests should fail silently.
                if (CLI_SCRIPT) {
                    // In CLI context, output error and exit.
                    cli_error("Error: Custom field creation failed: " . $e->getMessage());
                }
                // In web context, silently ignore to prevent errors from being displayed to users.
                // The function will simply return without creating the field.
            }

            // Store license key in database.
            set_config('edd_' . $this->pluginslug . '_license_key', $licensekey, 'local_edwiserpagebuilder');

            // Make API call to validate license.
            $curl = new \curl();
            $options = [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_USERAGENT' => (isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : 'Moodle') . ' - ' . $CFG->wwwroot,
                'CURLOPT_TIMEOUT' => 30,
            ];
            $postdata = [
                'edd_action' => 'activate_license',
                'license' => $licensekey,
                'item_name' => urlencode($this->pluginname),
                'current_version' => $this->pluginversion,
                'url' => urlencode($CFG->wwwroot),
            ];
            $resp = $curl->post($this->storeurl, $postdata, $options);
            $currentresponsecode = $curl->info['http_code'];

            $licensedata = json_decode($resp);
            $validresponsecode = ['200', '301'];

            $isdataavailable = $this->check_if_no_data($licensedata, $currentresponsecode, $validresponsecode);

            if ($isdataavailable) {
                $licensestatus = $this->status_update($licensedata);
                $this->set_transient_on_activation($licensestatus);
            }
        }
    }

    /**
     * Set transient on activation
     * @param string $licensestatus License status
     */
    public function set_transient_on_activation($licensestatus) {
        global $DB;

        $transexpired = false;

        // Check license trans.
        $transvar = get_config('local_edwiserpagebuilder', 'wdm_' . $this->pluginslug . '_license_trans');

        if ($transvar) {
            $transvar = json_decode($transvar, true);

            if (is_array($transvar) && time() > $transvar[1] && $transvar[1] > 0) {
                $transexpired = true;

                // Delete previous record.
                try {
                    unset_config('wdm_' . $this->pluginslug . '_license_trans', 'local_edwiserpagebuilder');
                } catch (dml_exception $e) {
                    $rcd = 0;
                    // Keep catch empty if no record found.
                }
            }
        } else {
            $transexpired = true;
        }

        if ($transexpired == false) {
            // Delete previous license trans.
            try {
                unset_config('wdm_' . $this->pluginslug . '_license_trans', 'local_edwiserpagebuilder');
            } catch (dml_exception $e) {
                $rcd = 0;
                // Keep catch empty if no record found.
            }

            if (! empty($licensestatus)) {
                if ($licensestatus == 'valid') {
                    $time = time() + 60 * 60 * 24 * 7;
                } else {
                    $time = time() + 60 * 60 * 24;
                }

                // Insert new license trans.
                set_config(
                    'wdm_' . $this->pluginslug . '_license_trans',
                    json_encode([$licensestatus, $time]),
                    'local_edwiserpagebuilder'
                );
            }
        }
    }

    /**
     * Deactivate theme license
     */
    public function deactivate_license() {
        global $DB, $CFG;

        $wpeplicensekey = get_config('local_edwiserpagebuilder', 'edd_' . $this->pluginslug . '_license_key');

        if (!empty($wpeplicensekey)) {
            // Make API call to deactivate license.
            $curl = new \curl();
            $options = [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_USERAGENT' => (isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : 'Moodle') . ' - ' . $CFG->wwwroot,
                'CURLOPT_TIMEOUT' => 30,
            ];
            $postdata = [
                'edd_action' => 'deactivate_license',
                'license' => $wpeplicensekey,
                'item_name' => urlencode($this->pluginname),
                'current_version' => $this->pluginversion,
                'url' => urlencode($CFG->wwwroot),
            ];
            $resp = $curl->post($this->storeurl, $postdata, $options);
            $currentresponsecode = $curl->info['http_code'];

            $licensedata = json_decode($resp);
            $validresponsecode = ['200', '301'];

            $isdataavailable = $this->check_if_no_data($licensedata, $currentresponsecode, $validresponsecode);

            if ($isdataavailable) {
                if ($licensedata->license == 'deactivated' || $licensedata->license == 'failed') {
                    // Delete previous record.
                    try {
                        unset_config('edd_' . $this->pluginslug . '_license_status', 'local_edwiserpagebuilder');
                    } catch (dml_exception $e) {
                        // No record found, safe to ignore.
                        // Handle exception based on context: CLI scripts need error output, web requests should fail silently.
                        if (CLI_SCRIPT) {
                            // In CLI context, output error and exit.
                            cli_error("Error: Custom field creation failed: " . $e->getMessage());
                        }
                        // In web context, silently ignore to prevent errors from being displayed to users.
                        // The function will simply return without creating the field.
                    }

                    // Update license status in database.
                    set_config('edd_' . $this->pluginslug . '_license_status', 'deactivated', 'local_edwiserpagebuilder');

                    // Update transient data.
                    try {
                        unset_config('wdm_' . $this->pluginslug . '_license_trans', 'local_edwiserpagebuilder');
                    } catch (dml_exception $e) {
                        // No record found, safe to ignore.
                        // Handle exception based on context: CLI scripts need error output, web requests should fail silently.
                        if (CLI_SCRIPT) {
                            // In CLI context, output error and exit.
                            cli_error("Error: Custom field creation failed: " . $e->getMessage());
                        }
                        // In web context, silently ignore to prevent errors from being displayed to users.
                        // The function will simply return without creating the field.
                    }

                    set_config(
                        'wdm_' . $this->pluginslug . '_license_trans',
                        json_encode(['deactivated', 0]),
                        'local_edwiserpagebuilder'
                    );
                }
            }
        }
    }

    /**
     * Validate and add data to database
     */
    public function add_data() {
        if (is_siteadmin()) {
            // Check if submission came from license page.
            $onlicensepage = optional_param('onRapidGraderLicensePage', 0, PARAM_BOOL);
            if (!$onlicensepage) {
                return;
            }

            // Validate sesskey to prevent CSRF attacks.
            require_sesskey();

            // Get license key from form.
            $lk = optional_param('edd_' . $this->pluginslug . '_license_key', '', PARAM_TEXT);
            if (empty($lk)) {
                $lk = 'empty';
            }

            // Handle license activation.
            $activateparam = optional_param('edd_' . $this->pluginslug . '_license_activate', '', PARAM_TEXT);
            $deactivateparam = optional_param('edd_' . $this->pluginslug . '_license_deactivate', '', PARAM_TEXT);

            if ($activateparam !== '') {
                set_config('licensekey', $lk, 'local_edwiserpagebuilder');
                set_config('licensekeyactivate', $activateparam, 'local_edwiserpagebuilder');
                return $this->activate_license();
            } else if ($deactivateparam !== '') {
                // Handle license deactivation.
                set_config('licensekey', $lk, 'local_edwiserpagebuilder');
                set_config(
                    'licensekeydeactivate',
                    $deactivateparam,
                    'local_edwiserpagebuilder',
                );
                return $this->deactivate_license();
            }
        }
    }

    /**
     * Get data from database
     * @return string License status
     */
    public function get_data_from_db() {
        global $CFG;

        if (null !== self::$responsedata) {
            return self::$responsedata;
        }

        $transexpired = false;

        $gettrans = get_config('local_edwiserpagebuilder', 'wdm_' . $this->pluginslug . '_license_trans');
        if ($gettrans) {
            $gettrans = json_decode($gettrans, true);
            if (is_array($gettrans) && time() > $gettrans[1] && $gettrans[1] > 0) {
                $transexpired = true;
                // Delete previous license trans.
                try {
                    $lusers = self::edd_get_users_from_api();
                    if (!empty($lusers) && isset($lusers->users)) {
                        set_config(
                            'epb_edwiser-pagebuilder_licensed_users',
                            json_encode($lusers->users),
                            'local_edwiserpagebuilder'
                        );
                    }
                    unset_config('wdm_' . $this->pluginslug . '_license_trans', 'local_edwiserpagebuilder');
                } catch (dml_exception $e) {
                    $rcd = 0;
                    // Keep catch empty if no record found.
                }
            }
        } else {
            $transexpired = true;
        }

        if ($transexpired == true) {
            $licensekey = get_config('local_edwiserpagebuilder', 'edd_' . $this->pluginslug . '_license_key');

            if ($licensekey) {
                // Get cURL resource.
                $curl = new \curl();
                $options = [
                    'CURLOPT_RETURNTRANSFER' => true,
                    'CURLOPT_USERAGENT' => (isset($_SERVER['HTTP_USER_AGENT']) ?
                        $_SERVER['HTTP_USER_AGENT'] : 'Moodle') . ' - ' . $CFG->wwwroot,
                    'CURLOPT_TIMEOUT' => 30,
                ];
                $postdata = [
                    'edd_action' => 'check_license',
                    'license' => $licensekey,
                    'item_name' => urlencode($this->pluginname),
                    'current_version' => $this->pluginversion,
                    'url' => urlencode($CFG->wwwroot),
                ];
                $resp = $curl->post($this->storeurl, $postdata, $options);

                $currentresponsecode = $curl->info['http_code'];

                $licensedata = json_decode($resp);

                $validresponsecode = ['200', '301'];

                if ($licensedata == null || ! in_array($currentresponsecode, $validresponsecode)) {
                    // If server does not respond, read current license information.
                    $licensestatus = get_config('local_edwiserpagebuilder', 'edd_' . $this->pluginslug . '_license_status');

                    if (empty($licensedata)) {
                        // Insert new license transient.
                        set_config(
                            'wdm_' . $this->pluginslug . '_license_trans',
                            json_encode(['server_did_not_respond', time() + (60 * 60 * 24)]),
                            'local_edwiserpagebuilder'
                        );
                    }
                } else {
                    $licensestatus = $licensedata->license;
                }

                if (empty($licensestatus)) {
                    return;
                }

                if (isset($licensedata->license) && ! empty($licensedata->license)) {
                    // Delete previous record.
                    try {
                        unset_config('edd_' . $this->pluginslug . '_license_status', 'local_edwiserpagebuilder');
                    } catch (dml_exception $e) {
                        $rcd = 0;
                        // Keep catch empty if no record found.
                    }

                    set_config(
                        'edd_' . $this->pluginslug . '_license_status',
                        $licensestatus,
                        'local_edwiserpagebuilder'
                    );
                }

                $this->set_response_data($licensestatus, $this->pluginslug, true);
                return self::$responsedata;
            }
        } else {
            $licensestatus = get_config('local_edwiserpagebuilder', 'edd_' . $this->pluginslug . '_license_status');

            $this->set_response_data($licensestatus, $this->pluginslug);
            return self::$responsedata;
        }
    }

    /**
     * Set response data to plugin config
     * @param string  $licensestatus License status
     * @param string  $pluginslug    Plugin slug
     * @param boolean $settransient  Transient data
     */
    public function set_response_data($licensestatus, $pluginslug, $settransient = false) {

        if ($licensestatus == 'valid') {
            self::$responsedata = 'available';
        } else if ($licensestatus == 'expired') {
            self::$responsedata = 'available';
        } else {
            self::$responsedata  = 'unavailable';
        }

        if ($settransient) {
            if ($licensestatus == 'valid') {
                $time = 60 * 60 * 24 * 7;
            } else {
                $time = 60 * 60 * 24;
            }

            // Delete previous record.
            try {
                unset_config('wdm_' . $pluginslug . '_license_trans', 'local_edwiserpagebuilder');
            } catch (dml_exception $e) {
                $rcd = 0;
                // Keep catch empty if no record found.
            }

            // Insert new license transient.
            set_config(
                'wdm_' . $pluginslug . '_license_trans',
                json_encode([$licensestatus, time() + (60 * 60 * 24)]),
                'local_edwiserpagebuilder'
            );
        }
    }
    /**
     * Get licensed users from api call
     * @return array Users list
     */
    public function edd_get_users_from_api() {
        global $CFG;
        $pluginslug = $this->pluginslug;
        $licensekey = get_config('local_edwiserpagebuilder', 'edd_' . $pluginslug . '_license_key');
        $status = get_config('local_edwiserpagebuilder', 'edd_' . $pluginslug . '_license_status');
        if ($licensekey && $status == 'valid') {
            $site = parse_url($CFG->wwwroot, PHP_URL_HOST) . '' . parse_url($CFG->wwwroot, PHP_URL_PATH);
            $curl = new \curl();
            $options = [
                'CURLOPT_HTTPHEADER' => ['Content-Type: application/json'],
                'CURLOPT_ENCODING' => "",
                'CURLOPT_MAXREDIRS' => 10,
                'CURLOPT_TIMEOUT' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            ];
            $url = "https://edwiser.org/wp-json/wdm-eddc/v1/potential-users/get?key=" . $licensekey . "&site=" . $site;
            $resp = $curl->get($url, [], $options);
            return json_decode($resp);
        }
    }
}
