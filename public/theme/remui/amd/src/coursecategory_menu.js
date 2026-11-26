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
 * Lazy-load course category dropdown menu via AJAX.
 *
 * @module     theme_remui/coursecategory_menu
 * @copyright  (c) 2023 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    /**
     * Load category HTML into a menu element.
     *
     * @param {jQuery} $target The element to populate (dropdown-menu or collapse).
     * @param {jQuery} $toggle The toggle element to mark as loaded.
     */
    var loadCategories = function($target, $toggle) {
        // Already loaded.
        if ($toggle.attr('data-loaded') === '1') {
            return;
        }

        // Inject loader.
        $target.html(
            '<div class="d-flex justify-content-center align-items-center p-3" style="min-height:120px;">' +
                '<img src="' + M.cfg.wwwroot + '/theme/remui/pix/siteinnerloader.svg" ' +
                    'style="width:24px;height:24px;" alt="">' +
            '</div>'
        );

        // Call WS.
        var promise = Ajax.call([{
            methodname: 'theme_remui_get_course_category_menu',
            args: {}
        }], true, true);

        promise[0].done(function(response) {
            if (response.status && response.html) {
                $target.html(response.html);
                $toggle.attr('data-loaded', '1');
            } else {
                $target.empty();
            }
        }).fail(function(error) {
            $target.empty();
            Notification.exception(error);
        });
    };

    /**
     * Initialize lazy-load for category dropdowns.
     */
    var init = function() {
        // Use event delegation so dynamically added elements (e.g. after AJAX re-renders in edit mode) work.

        // Desktop dropdowns: data-lazyloadcategory is on the toggle <a> inside .dropdown.
        $(document).on('show.bs.dropdown', '[data-lazyloadcategory="1"]', function() {
            var $toggle = $(this);
            var $dropdown = $toggle.closest('.dropdown');
            if (!$dropdown.length) {
                return;
            }
            var $menu = $dropdown.find('.dropdown-menu').first();
            loadCategories($menu, $toggle);
        });

        // Fallback: shown.bs.dropdown fires after the dropdown is visible.
        $(document).on('shown.bs.dropdown', '[data-lazyloadcategory="1"]', function() {
            var $toggle = $(this);
            var $dropdown = $toggle.closest('.dropdown');
            if (!$dropdown.length) {
                return;
            }
            var $menu = $dropdown.find('.dropdown-menu').first();
            if ($toggle.attr('data-loaded') !== '1') {
                loadCategories($menu, $toggle);
            }
        });

        // Fallback: direct click handler in case Bootstrap events are suppressed.
        $(document).on('click', '[data-lazyloadcategory="1"]', function() {
            var $toggle = $(this);
            var $dropdown = $toggle.closest('.dropdown');
            if ($dropdown.length) {
                var $menu = $dropdown.find('.dropdown-menu').first();
                if ($toggle.attr('data-loaded') !== '1') {
                    loadCategories($menu, $toggle);
                }
            }
        });

        // Prevent the dropdown from closing when clicking a collapse toggle inside it.
        $(document).on('hide.bs.dropdown', '[data-lazyloadcategory="1"]', function(e) {
            var clickEvent = e.clickEvent || (e.relatedTarget && e.relatedTarget.clickEvent);
            if (clickEvent && $(clickEvent.target).closest('[data-bs-toggle="collapse"]').length) {
                e.preventDefault();
            }
        });

        // Mobile collapses: data-lazyloadcategory is on the .collapse div itself.
        $(document).on('show.bs.collapse', '[data-lazyloadcategory="1"]', function() {
            var $collapse = $(this);
            loadCategories($collapse, $collapse);
        });

        // If any dropdown is already shown (e.g. rendered with 'show' class) but not loaded yet, load it now.
        $('[data-lazyloadcategory="1"]').each(function() {
            var $toggle = $(this);
            var $dropdown = $toggle.closest('.dropdown');
            if ($dropdown.length) {
                var $menu = $dropdown.find('.dropdown-menu').first();
                if ($menu.hasClass('show') && $toggle.attr('data-loaded') !== '1') {
                    loadCategories($menu, $toggle);
                }
            }
            if ($toggle.hasClass('collapse') && $toggle.hasClass('show') && $toggle.attr('data-loaded') !== '1') {
                loadCategories($toggle, $toggle);
            }
        });
    };

    return {
        init: init
    };
});
