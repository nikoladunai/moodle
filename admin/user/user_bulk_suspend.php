<?php

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);
admin_externalpage_setup('userbulk');
require_capability('moodle/user:update', context_system::instance());
$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}
echo $OUTPUT->header();

if ($confirm and confirm_sesskey()) {
    echo get_string('suspendedusers');
    $html = html_writer::start_tag('ul', $SESSION->bulk_users);
    foreach ($SESSION->bulk_users as $user_id) {
        $user = $DB->get_record('user', ['id' => $user_id]);
        $user->suspended = 1;
        \core\session\manager::kill_user_sessions($user->id);
        user_update_user($user, false);
        unset($SESSION->bulk_users[$user_id]);
        $html .= html_writer::tag('li', fullname($user));
    }
    $html .= html_writer::end_tag('ul');
    echo $html;
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);
} else {
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcont = new single_button(new moodle_url('/admin/user/user_bulk_suspend.php', ['confirm' => 1]), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/admin/user/user_bulk.php'), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('suspendquestion'), $formcont, $formcancel);
}
echo $OUTPUT->footer();
