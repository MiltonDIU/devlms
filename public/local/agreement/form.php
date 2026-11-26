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
 * @package   local_agreement
 * @copyright 2020, Md. Milton <milton2913@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// require config.php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE,$OUTPUT,$USER,$CFG;

$PAGE->requires->css('/local/agreement/popup/style.css');
$PAGE->requires->js(new moodle_url('/local/agreement/popup/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/agreement/popup/bootstrap.min.js'),true);


require_once($CFG->dirroot . '/local/agreement/classes/form/agreement.php');





// define the context of the page, for more info read this: https://docs.moodle.org/dev/Page_API#Setting_up_the_page
$context = context_system::instance();

/* Check user is logged_in */
if (!isloggedin()) {
    redirect(get_login_url());
}

/* Check user is teacher */
$is_teacher = user_has_role_assignment($USER->id,3);

if(!$is_teacher){
    redirect($CFG->wwwroot . '/local/agreement/index.php', 'You do not have permission to view this page');
        exit();
}

$sql = "SELECT * FROM {local_agreements} la where la.user_id = $USER->id";
$user = $DB->get_records_sql($sql);
if (count($user)>0){
        //header('Location: ../agreement/index.php');
    redirect($CFG->wwwroot . '/local/agreement/index.php', 'You have already agreed with the policy clauses below');
        exit();
}

$PAGE->set_url(new moodle_url('/local/agreement/form.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Agreement Form');
// We want to display our form.
$mform = new agreement();
if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/agreement/index.php',  'You cancelled the policy clauses below');
} else if ($fromform = $mform->get_data()){
    $currentTime = time();
    // converts the time in seconds to current date
    $created_at = date('Y-m-d h:i:s', $currentTime);

    // new object.
    $data = new stdClass();
//    $data->is_agreed = $fromform->is_agreed;
    if(!empty($fromform->submitbutton)) {
        if (isset($fromform->is_agreed)){
            $data->is_agreed =$data->is_agreed = 'Yes';
        }else{
            $data->is_agreed  =  $data->is_agreed = 'No';
        }
    }
    $data->user_id = $USER->id;
    $data->created_at = $created_at;
    // Insert the data into  database table.
    $DB->insert_record('local_agreements', $data);
    // Go back to message.php page
    redirect($CFG->wwwroot . '/local/agreement/index.php', 'Thanks for agreeing with the policy' . $fromform->messagetext);
}
echo $OUTPUT->header();
$templatecontext = (object)[
    'heading' => "Policy:",
];
echo $OUTPUT->render_from_template('local_agreement/index', $templatecontext);
?>

    <script>
        $(document).ready(function(){
            $("#myModal").modal('show').css('padding','0px');

        });
    </script>
    <div id="myModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <div class="container-fluid">
                        <p>
                            Agreement between Faculty Member (Full time/Contractual/Ad‐hoc/Part‐time) of Daffodil International University and Daffodil International University on course contents uploaded to the Blended Learning Center (BLC)
                        </p>
                        <h3>All faculty members of Daffodil International University agree to:</h3>
                        <ol>

 <li> All content uploaded to the BLC, including intellectual property and derivative works created using university resources, is owned by DIU. Faculty retain moral rights (e.g., attribution); economic rights (e.g., reproduction) transfer to DIU.</li>
                          <li>Unauthorized use of BLC resources, including sharing credentials, granting third-party access, or distributing content without permission, is prohibited. Violations may lead to disciplinary or legal action.</li>
                          <li>Accounts must use strong passwords (minimum 12 characters, with uppercase, lowercase, numbers, and special characters). Two-factor authentication is recommended. Faculty are liable for breaches due to non-compliance.</li>
                          <li>Uploading copyrighted material without authorization is forbidden. Faculty must ensure legal rights to content. DIU may remove infringing material; faculty are liable for violations.</li>
                          <li>Faculty must protect DIU’s intellectual property on the BLC, preventing misuse or unauthorized transfer, to uphold the university’s reputation and rankings.</li>
                          <li>BLC course content is restricted to enrolled students and authorized administrators. Faculty must password-protect courses to prevent public access.</li>
                          <li>Faculty indemnify DIU against claims from copyright infringement, unauthorized use, or account security failures.</li>
                          <li>DIU may update these terms, notifying faculty, who must comply with revisions.</li>
                        </ol>
                    </div>
                   <?php $mform->display();?>
                </div>
            </div>
        </div>
    </div>

<?php $mform->display();?>
<script>
    document.getElementById("id_submitbutton").disabled = true;
    var id_is_agreed = document.getElementById('id_is_agreed');
    var agree_button = document.getElementById('id_submitbutton');
    id_is_agreed.onchange = function(){
        if(this.checked){
            agree_button.disabled = false;
        } else {
            agree_button.disabled = true;
        }
    }
</script>
<?php
echo $OUTPUT->footer();
