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
 * AI block content generator using Moodle's core AI subsystem.
 *
 * Migrated from blocks/edwiseradvancedblock/classes/external/generate_ai_content.php
 * and enhanced with prompt optimization capability.
 *
 * @package   local_edwiserpagebuilder
 * @copyright (c) 2026 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_edwiserpagebuilder\ai;

/**
 * Generates block content using Moodle's core AI manager.
 *
 * @package local_edwiserpagebuilder
 */
class ai_block_generator {
    /**
     * Check if Moodle's core AI subsystem is available (Moodle 4.5+).
     *
     * @return bool True if core_ai is available.
     */
    public static function is_ai_available(): bool {
        return class_exists('\core_ai\manager');
    }

    /**
     * Check if an AI provider is configured and active.
     *
     * @return bool True if at least one AI provider is configured.
     */
    public static function is_ai_provider_configured(): bool {
        if (!self::is_ai_available()) {
            return false;
        }
        try {
            $manager = \core\di::get(\core_ai\manager::class);
            $actionclass = \core_ai\aiactions\generate_text::class;
            // Check action is available and has providers (pattern from aiplacement_courseassist).
            if (!$manager->is_action_available($actionclass)) {
                return false;
            }
            $providers = $manager->get_providers_for_actions([$actionclass], true);
            return !empty($providers[$actionclass]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Generate block content (HTML/CSS/JS) from a user prompt.
     *
     * @param int $contextid The Moodle context ID.
     * @param string $prompttext The user's prompt (already validated).
     * @return array {success: bool, html: string, css: string, js: string, combined: string, error: string}
     */
    public static function generate_content(int $contextid, string $prompttext): array {
        global $USER;

        if (!self::is_ai_available()) {
            return [
                'success' => false,
                'html' => '',
                'css' => '',
                'js' => '',
                'combined' => '',
                'error' => \get_string('ai_not_available', 'local_edwiserpagebuilder'),
            ];
        }

        // Enhance the prompt for structured AI output.
        $enhancedprompt = self::build_generation_prompt($prompttext);

        try {
            $action = new \core_ai\aiactions\generate_text(
                contextid: $contextid,
                userid: $USER->id,
                prompttext: $enhancedprompt,
            );

            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);

            if (!$response->get_success()) {
                return [
                    'success' => false,
                    'html' => '',
                    'css' => '',
                    'js' => '',
                    'combined' => '',
                    'error' => $response->get_errormessage() ?: \get_string('ai_generation_failed', 'local_edwiserpagebuilder'),
                ];
            }

            $raw = $response->get_response_data()['generatedcontent'] ?? '';

            // Parse the AI response for [HTML], [CSS], [JS] sections.
            $html = self::extract_section($raw, 'HTML');
            $css = self::extract_section($raw, 'CSS');
            $js = self::extract_section($raw, 'JS');

            // Combine into a single HTML string with inline style/script.
            $combined = self::combine_content($html, $css, $js);

            return [
                'success' => true,
                'html' => $html,
                'css' => $css,
                'js' => $js,
                'combined' => $combined,
                'error' => '',
            ];
        } catch (\Throwable $e) {
            debugging('AI block generation failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'html' => '',
                'css' => '',
                'js' => '',
                'combined' => '',
                'error' => \get_string('ai_generation_failed', 'local_edwiserpagebuilder'),
            ];
        }
    }

    /**
     * Optimize a user prompt using AI to make it more effective.
     *
     * @param int $contextid The Moodle context ID.
     * @param string $prompttext The user's original prompt.
     * @return array {success: bool, optimized_prompt: string, error: string}
     */
    public static function optimize_prompt(int $contextid, string $prompttext): array {
        global $USER;

        if (!self::is_ai_available()) {
            return [
                'success' => false,
                'optimized_prompt' => '',
                'error' => \get_string('ai_not_available', 'local_edwiserpagebuilder'),
            ];
        }

        $metaprompt = self::build_optimization_prompt($prompttext);

        try {
            $action = new \core_ai\aiactions\generate_text(
                contextid: $contextid,
                userid: $USER->id,
                prompttext: $metaprompt,
            );

            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);

            if (!$response->get_success()) {
                return [
                    'success' => false,
                    'optimized_prompt' => '',
                    'error' => $response->get_errormessage() ?: \get_string('ai_optimization_failed', 'local_edwiserpagebuilder'),
                ];
            }

            $optimized = trim($response->get_response_data()['generatedcontent'] ?? '');

            // Clean up any markdown formatting the AI might have added.
            $backticks = chr(96) . '{3}';
            $optimized = preg_replace('/^' . $backticks . '.*\n?/', '', $optimized);
            $optimized = preg_replace('/\n?' . $backticks . '$/', '', $optimized);
            $optimized = trim($optimized);

            if (empty($optimized)) {
                return [
                    'success' => false,
                    'optimized_prompt' => '',
                    'error' => \get_string('ai_optimization_empty', 'local_edwiserpagebuilder'),
                ];
            }

            // Hard-enforce the 2000 character limit regardless of AI output.
            if (\core_text::strlen($optimized) > 2000) {
                // Truncate at the last sentence boundary within 2000 chars.
                $truncated = \core_text::substr($optimized, 0, 2000);
                $lastperiod = strrpos($truncated, '.');
                if ($lastperiod !== false && $lastperiod > 1000) {
                    $optimized = \core_text::substr($truncated, 0, $lastperiod + 1);
                } else {
                    $optimized = $truncated;
                }
            }

            return [
                'success' => true,
                'optimized_prompt' => $optimized,
                'error' => '',
            ];
        } catch (\Throwable $e) {
            debugging('AI prompt optimization failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'optimized_prompt' => '',
                'error' => \get_string('ai_optimization_failed', 'local_edwiserpagebuilder'),
            ];
        }
    }

    /**
     * Build the enhanced prompt for HTML/CSS/JS generation.
     *
     * Preserves the POC's prompt engineering pattern.
     *
     * @param string $userprompt The user's prompt.
     * @return string The enhanced prompt.
     */
    private static function build_generation_prompt(string $userprompt): string {
        return \get_string('ai_generation_prompt', 'local_edwiserpagebuilder', $userprompt);
    }

    /**
     * Build the meta-prompt for prompt optimization.
     *
     * @param string $userprompt The user's original prompt.
     * @return string The meta-prompt for optimization.
     */
    private static function build_optimization_prompt(string $userprompt): string {
        // Calculate available budget: the optimized output must be <= 2000 chars.
        $inputlength = \core_text::strlen($userprompt);
        $budgetnote = \get_string('ai_optimization_budgetnote', 'local_edwiserpagebuilder', $inputlength);

        return \get_string('ai_optimization_prompt', 'local_edwiserpagebuilder', (object)[
            'budgetnote' => $budgetnote,
            'userprompt' => $userprompt,
        ]);
    }

    /**
     * Extract a section from the AI response text.
     *
     * Preserved from POC: blocks/edwiseradvancedblock/classes/external/generate_ai_content.php
     *
     * @param string $text The full AI response text.
     * @param string $section The section name (HTML, CSS, JS).
     * @return string The extracted section content.
     */
    private static function extract_section(string $text, string $section): string {
        if (preg_match("/\\[$section\\](.*?)\\[\\/$section\\]/si", $text, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Combine HTML, CSS, and JS into a single content string.
     *
     * This adapts the POC's separate-field storage to EPB's single content field
     * in the local_edwiserpagebuilder_blocks table.
     *
     * @param string $html The HTML content.
     * @param string $css The CSS content.
     * @param string $js The JS content.
     * @return string Combined content with inline style and script tags.
     */
    private static function combine_content(string $html, string $css, string $js): string {
        $parts = [];

        if (!empty($css)) {
            $parts[] = '<style>' . $css . '</style>';
        }

        if (!empty($html)) {
            $parts[] = $html;
        }

        if (!empty($js)) {
            $parts[] = '<script>' . $js . '</script>';
        }

        return implode("\n", $parts);
    }
}
