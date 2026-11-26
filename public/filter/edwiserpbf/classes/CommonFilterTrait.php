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

namespace filter_edwiserpbf;

/**
 * Class common_filter
 *
 * @package    filter_edwiserpbf
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Trait providing common functionality for filtering text and replacing shortcodes.
 *
 * This trait provides methods for filtering text and replacing various types of shortcodes, including:
 * - Courses shortcodes
 * - Categories shortcodes
 * - CNC (Courses and Categories) shortcodes
 * - Featured courses shortcodes
 * - Remuiblck shortcodes
 *
 * The `filter_tags()` method is used to convert the shortcode tags into an associative array.
 * The `filter()` method is the main entry point for filtering the text and replacing the shortcodes.
 */
trait CommonFilterTrait {
    /**
     * Filter tags and convert to associative array.
     *
     * @param  array $tags Tags array.
     * @return array       Tags array.
     */
    private function filter_tags($tags) {
        $assoarr = [];
        for ($i = 1; $i < count($tags); $i++) {
            $assoarr[$tags[$i][0]] = $tags[$i + 1][0];
            $i = $i + 1;
        }
        return $assoarr;
    }

    /**
     * Filter the text and replace edwiser shortcodes with generated content.
     *
     * @param string $text The text to filter.
     * @param array $options Filter options.
     * @return string The filtered text.
     */
    public function filter($text, array $options = []) {

        if (!is_string($text) || empty($text)) {
            return $text;
        }

        $edwisershortcode = "[edwiser-";

        if (strpos($text, $edwisershortcode) === false) {
            return $text;
        }

        // Context guard: only expand shortcodes in contexts where students cannot inject them.
        // This prevents students from triggering heavy queries by putting shortcodes in forum posts,
        // assignment submissions, user profiles, and other user-generated content areas.
        // Teacher-only resource modules (Page, Book, Label, etc.) are still allowed.
        if (isset($options['context'])) {
            $context = $options['context'];
            $allowedlevels = [
                CONTEXT_SYSTEM,
                CONTEXT_COURSECAT,
                CONTEXT_COURSE,
                CONTEXT_BLOCK,
            ];

            if (!in_array($context->contextlevel, $allowedlevels)) {
                // For CONTEXT_MODULE, allow only teacher-only resource types.
                if ($context->contextlevel == CONTEXT_MODULE) {
                    $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, IGNORE_MISSING);
                    if (!$cm) {
                        return $text;
                    }
                    $allowedmods = ['page', 'book', 'label', 'resource', 'url'];
                    if (!in_array($cm->modname, $allowedmods)) {
                        return $text;
                    }
                } else {
                    return $text;
                }
            }
        }
        // Courses pregmatch.
        $pregmatch = "(\[edwiser\-courses[ ]+(catid)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9,]+)"
        . "[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d][ ](layout)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]\])";

        preg_match_all(
            $pregmatch,
            $text,
            $tags
        );

        // Find the replacement text.
        if (isset($tags[0][0]) && $tags[0][0] != "") {
            $replace = $tags[0][0];

            // Filter the shortcode tags to object.
            $tags = $this->filter_tags($tags);

            // Content generation.
            $cg = new ContentGenerator();
            $content = $cg->generate_courses($tags);

            $text = str_replace($replace, $content, $text);
        }

        // Categories pregmatch.
        $pregmatch = "(\[edwiser\-categories[ ]+(layout)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]*)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](btnlabel)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](count)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]\])";

        preg_match_all(
            $pregmatch,
            $text,
            $tags
        );

        // Find the replacement text.
        if (isset($tags[0][0]) && $tags[0][0] != "") {
            $replace = $tags[0][0];

            // Filter the shortcode tags to object.
            $tags = $this->filter_tags($tags);

            // Content generation.
            $cg = new ContentGenerator();
            $content = $cg->generate_categories($tags);

            $text = str_replace($replace, $content, $text);
        }

        // Categories pregmatch.
        $pregmatch = "(\[edwiser\-cnc[ ]+(layout)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]*)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](show)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](catid)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9,]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](date)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]\])";

        preg_match_all(
            $pregmatch,
            $text,
            $tags
        );
        // Find the replacement text.
        if (isset($tags[0][0]) && $tags[0][0] != "") {
            $replace = $tags[0][0];

            // Filter the shortcode tags to object.
            $tags = $this->filter_tags($tags);

            // Content generation.
            $cg = new ContentGenerator();
            $content = $cg->generate_cnc_content($tags);

            $text = str_replace($replace, $content, $text);
        }

        // Featured courses pregmatch.
        $pregmatch = "(\[edwiser\-fc[ ]+(layout)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]*)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](show)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](courseid)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9,]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]"
        . "[ ](date)\=[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]([a-zA-Z0-9]+)[\'\"'\xe2\x80\x99\xe2\x80\x98\xe2\x80\x9c\xe2\x80\x9d]\])";

        preg_match_all(
            $pregmatch,
            $text,
            $tags
        );
        // Find the replacement text.
        if (isset($tags[0][0]) && $tags[0][0] != "") {
            $replace = $tags[0][0];

            // Filter the shortcode tags to object.
            $tags = $this->filter_tags($tags);

            // Content generation.
            $cg = new ContentGenerator();
            $content = $cg->generate_fc_content($tags);
            $text = str_replace($replace, $content, $text);
        }

        // Remuiblck.
        $pregmatch = '/\[edwiser-remuiblck-[^\]]*\]/U';
        preg_match_all(
            $pregmatch,
            $text,
            $matches,
            PREG_SET_ORDER
        );

        $tags = [];

        foreach ($matches as $key => $match) {
            $tags[$key] = [$match[0]];

            // Extract attribute names and values.
            preg_match_all('/(\w+)\s*=\s*["\']([^"\']+)["\']/', $match[0], $attributes, PREG_SET_ORDER);

            // Output attribute names and values.
            foreach ($attributes as $attribute) {
                $attrname = $attribute[1];
                $attrvalue = clean_param($attribute[2], PARAM_TEXT);
                $tags[$key][] = [$attrname];
                $tags[$key][] = [$attrvalue];
            }
        }

        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                // Find the replacement text.
                if (isset($tag[0]) && $tag[0] != "") {
                    $replace = $tag[0];

                    // Filter the shortcode tags to object.
                    $tag = $this->filter_tags($tag);

                    // Content generation.
                    $cg = new ContentGenerator();
                    $content = $cg->generate_remuiblck_content($tag);

                    $text = str_replace($replace, $content, $text);
                }
            }
        }

        return $text;
    }
}
