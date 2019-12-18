<?php

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('userbulk');
require_capability('moodle/user:update', context_system::instance());

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

global $DB;

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

echo $OUTPUT->header();

if ($confirm and confirm_sesskey()) {

    echo 'Suspended users: ';
    echo '<ul>';

//    foreach ($SESSION->bulk_users as $user_id) {
//        $user = $DB->get_record('user', array('id' => $user_id));
//        $user->suspended = "1";
//        $DB->update_record('user', $user, $bulk = false);
//        unset($SESSION->bulk_users[$user_id]);
//
//        echo '<li>' . $user->firstname . ' ' . $user->lastname . '</li>';
//    }

//    $select = "id IN (".implode(', ',$SESSION->bulk_users).")";
//    $result = $DB->get_records_select('user', $select);
//    foreach ($result as $user) {
//        $user->suspended = "1";
//        $DB->update_record('user', $user, $bulk=false);
//        echo '<li>'.$user->username.'</li>';
//    }

    $sql = "UPDATE mdl_user 
            SET suspended = '1' 
            WHERE id IN (".implode(', ', $SESSION->bulk_users).")";

    $DB->execute($sql, $params=null);

    $select = "id IN (".implode(', ',$SESSION->bulk_users).")";

    $rs = $DB->get_recordset_select('user', $select, $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0);
    foreach($rs as $record) {
        echo '<li>'.$record->username.'</li>';
        unset($SESSION->bulk_users[$record->id]);
    }
    $rs->close();

    echo '</ul>';

    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);

} else {
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('/admin/user/user_bulk_suspend.php', array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/admin/user/user_bulk.php'), get_string('no'), 'get');
    echo $OUTPUT->confirm('Are you sure you want to suspend selected users?', $formcontinue, $formcancel);
}

echo $OUTPUT->footer();
