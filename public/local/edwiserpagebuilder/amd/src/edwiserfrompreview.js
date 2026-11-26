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
 * Preview mode form rendering for Edwiser Page Builder.
 *
 * @module     local_edwiserpagebuilder/edwiserfrompreview
 * @copyright  (c) 2024 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('local_edwiserpagebuilder/edwiserfrompreview', ['local_edwiserpagebuilder/jquery', 'core/ajax', , 'local_edwiserform/iefixes', 'local_edwiserform/formviewer'], function ($, ajax) {
    function get_from_data(formid) {
        return ajax.call([{
            methodname: 'edwiserform_get_form_definition',
            args: {
                form_id: formid,
                countries: typeof window['countries'] == 'undefined' || true
            }
        }]);
    }

    render_form = (ele) => {
        var formid = $(ele)[0].dataset.formid;
        var formdata = get_from_data(formid);
        var rootcont = $(ele).find('.edwiserform-root-container').find('.edwiserform-wrap-container');
        var form = $(ele).find('.edwiserform-root-container').find('.edwiserform-wrap-container').find('.edwiserform-container');

        var formOptions = {
            container: form.get(0),
            sitekey: M.cfg.sesskey,
            countries: null,
            localStorage: false,
        };
        formdata[0].done(function (responce) {
            if (responce.status != false) {
                formOptions.countries = responce.countries;
                $(rootcont).prepend(`<h2 class='form-header'>${responce.title}</h2>`);
                var formeo = new Formeo(formOptions, responce.definition);
                formeo.render(form.get(0));
                // formStyles.apply($(form).find('.formeo-render'), 'add', responce.style);
            }
        });
    };
    render_all_form = () => {
        var editorbody = $("#iframe1").contents().find("body");
        $.each($(editorbody).find('.edwiser-pb-form-wrap'), function (index, ele) {
            render_form(this);
        });
    };

    return {
        render_all_form: this.render_all_form,
        render_form: this.render_form,
        init: function () {
            $(document).ready(function () {
                Vvveb.Builder.iframe.onload = function () { render_all_form(); };
            });
        }
    };
});
