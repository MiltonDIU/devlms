<?php
/* Check user is teacher  it's my custom only teacher redirect code*/
$is_teacher = user_has_role_assignment($USER->id,3);
if($is_teacher){
    $sql = "SELECT * FROM {local_agreements} la where la.user_id = $USER->id";
    $user = $DB->get_records_sql($sql);
    if (count($user)==0){
        redirect($CFG->wwwroot . '/local/agreement/form.php');
    }
}
//end my custom only teacher redirect code