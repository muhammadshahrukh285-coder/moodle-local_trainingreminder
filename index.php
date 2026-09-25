<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('local/trainingreminder:manage', $context);

$action = optional_param('action', 'list', PARAM_ALPHA);
$campid = optional_param('id', 0, PARAM_INT);
$tab = optional_param('tab', 'campaigns', PARAM_ALPHA); 

$PAGE->set_url(new moodle_url('/local/trainingreminder/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('Training Reminder Dashboard (Free)');
$PAGE->set_heading('Training Reminder (Free Edition)');
$PAGE->set_pagelayout('admin');

if ($action === 'savesettings' && confirm_sesskey()) {
    $master = optional_param('master_enable', 0, PARAM_INT);
    set_config('master_enable', $master, 'local_trainingreminder');
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'settings']), 'Global settings saved.', null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'save' && confirm_sesskey()) {
    $record = new stdClass();
    $record->name = required_param('name', PARAM_TEXT);
    $record->enabled = optional_param('enabled', 0, PARAM_INT);
    $record->target_type = 'courses'; 
    $record->target_ids = required_param('single_course_id', PARAM_INT); 
    $record->post_rule_enabled = 0; 
    $record->post_freq_days = 0; 
    $record->post_rule_msg = ''; 
    $record->timemodified = time();

    if ($campid > 0) {
        $record->id = $campid;
        $DB->update_record('local_trainingreminder_camps', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_trainingreminder_camps', $record);
    }
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), 'Campaign saved!', null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'delete' && $campid > 0 && confirm_sesskey()) {
    $DB->delete_records('local_trainingreminder_steps', ['campid' => $campid]);
    $DB->delete_records('local_trainingreminder_logs', ['campid' => $campid]);
    $DB->delete_records('local_trainingreminder_camps', ['id' => $campid]);
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), 'Campaign deleted.', null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'test' && $campid > 0 && confirm_sesskey()) {
    global $USER, $CFG;
    require_once($CFG->dirroot . '/message/lib.php');
    $step = $DB->get_record('local_trainingreminder_steps', ['campid' => $campid], '*', IGNORE_MULTIPLE);
    if (!$step) {
        redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), 'Add a rule first!', null, \core\output\notification::NOTIFY_WARNING);
        exit;
    }
    
    $body = str_replace(
        ['{{firstname}}', '{{lastname}}', '{{coursename}}', '{{incomplete_courses}}', '{{completed_courses}}', '{{siteurl}}'], 
        [$USER->firstname, $USER->lastname, 'Sample Course', '<ul><li>Sample Course</li></ul>', '<i>None</i>', $CFG->wwwroot], 
        $step->body_template
    );

    $message = new \core\message\message();
    $message->component = 'local_trainingreminder';
    $message->name = 'training_reminder';
    $message->userfrom = \core_user::get_noreply_user();
    $message->userto = $USER->id;
    $message->subject = "[TEST] " . str_replace('{{coursename}}', 'Sample Course', $step->subject);
    $message->fullmessage = strip_tags($body);
    $message->fullmessageformat = FORMAT_HTML;
    $message->fullmessagehtml = $body;
    $message->notification = 1;
    message_send($message);
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), 'Test email sent.', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

if ($action === 'edit' || $action === 'add') {
    $campaign = $campid > 0 ? $DB->get_record('local_trainingreminder_camps', ['id' => $campid]) : null;
    $name = $campaign ? $campaign->name : '';
    $enabled = $campaign ? $campaign->enabled : 1;
    $target_ids = $campaign ? $campaign->target_ids : 0;

    $formaction = new moodle_url('/local/trainingreminder/index.php', ['action' => 'save', 'id' => $campid]);
    $backurl = new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']);

    echo html_writer::start_tag('div', ['class' => 'container-fluid']);
    echo html_writer::link($backurl, '&larr; Back to Dashboard', ['class' => 'btn btn-secondary mb-3']);
    echo html_writer::start_tag('form', ['method' => 'POST', 'action' => $formaction]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo '<div class="card shadow-sm mb-4"><div class="card-body">';
    echo '<h4 class="card-title">Campaign Settings</h4><hr>';
    echo '<div class="form-group mb-3"><label><strong>Campaign Name</strong></label><input type="text" class="form-control" name="name" value="' . s($name) . '" required></div>';
    
    $checked = $enabled ? 'checked' : '';
    echo '<div class="form-group form-check mb-3"><input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" ' . $checked . '><label class="form-check-label" for="enabled">Enable this campaign</label></div>';

    echo '<div class="form-group mb-3"><label><strong>Target Audience</strong></label><select class="form-control" disabled>';
    echo '<option selected>Single Course (Free Version Limit)</option>';
    echo '<option>All Courses (PRO Version)</option>';
    echo '<option>Multiple Courses (PRO Version)</option>';
    echo '<option>Selected Categories (PRO Version)</option></select></div>';

    $allcourses = $DB->get_records_select('course', 'id != 1', null, 'fullname ASC', 'id, fullname');
    echo '<div class="form-group mb-3"><label><strong>Select Target Course:</strong></label><select class="form-control" name="single_course_id" required>';
    foreach ($allcourses as $c) {
        $sel = ($c->id == $target_ids) ? 'selected' : '';
        echo '<option value="'.$c->id.'" '.$sel.'>' . s($c->fullname) . '</option>';
    }
    echo '</select></div></div></div>';

    echo '<div class="card shadow-sm border-warning"><div class="card-body">';
    echo '<h4 class="card-title text-warning">Post-Rule Fallback <span class="badge badge-warning text-dark ml-2">PRO VERSION ONLY</span></h4><hr>';
    echo '<div class="alert alert-warning text-dark">Upgrade to <strong>Training Reminder PRO</strong> to unlock infinite recurring reminders!</div>';
    echo '<div class="form-group form-check mb-3"><input type="checkbox" class="form-check-input" disabled><label class="form-check-label text-muted">Enable Recurring Reminders (Infinite Loop)</label></div>';
    echo '<div class="form-group mb-3"><label class="text-muted"><strong>Frequency (Days)</strong></label><input type="number" class="form-control" disabled value="7"></div>';
    echo '<div class="form-group mb-3"><label class="text-muted"><strong>Fallback Message Template</strong></label><textarea class="form-control" disabled rows="3">Unlock PRO to write your custom infinite-loop email...</textarea></div>';
    echo '<button type="submit" class="btn btn-primary btn-lg mt-3">Save Campaign Settings</button>';
    echo '</div></div></form></div>';
} else {
    echo '<ul class="nav nav-tabs mb-4">';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'campaigns' ? 'active font-weight-bold' : '') . '" href="?tab=campaigns">Dashboard & Campaigns</a></li>';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'logs' ? 'active font-weight-bold' : '') . '" href="?tab=logs">Delivery Logs</a></li>';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'settings' ? 'active font-weight-bold' : '') . '" href="?tab=settings">Global Settings</a></li>';
    echo '</ul>';

    if ($tab === 'campaigns') {
        echo '<div class="row"><div class="col-md-12">';
        $total_camps = $DB->count_records('local_trainingreminder_camps');
        
        echo '<div class="d-flex justify-content-between align-items-center mb-3"><h4>Manage Campaigns</h4>';
        if ($total_camps >= 1) {
            echo '<a href="#" class="btn btn-sm btn-warning shadow-sm font-weight-bold" onclick="alert(\'FREE VERSION LIMIT REACHED: You can only have 1 active campaign. Upgrade to PRO for unlimited campaigns targeting every course!\'); return false;">+ Create New Campaign <span class="badge badge-dark">PRO</span></a>';
        } else {
            $addurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'add']);
            echo html_writer::link($addurl, '+ Create New Campaign', ['class' => 'btn btn-sm btn-primary shadow-sm']);
        }
        echo '</div>';

        $campaigns = $DB->get_records('local_trainingreminder_camps');
        $table = new html_table();
        $table->attributes['class'] = 'table table-bordered table-hover bg-white shadow-sm';
        $table->head = ['Campaign Name', 'Target', 'Status', 'Actions'];
        $table->data = [];

        foreach ($campaigns as $camp) {
            $editurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'edit', 'id' => $camp->id]);
            $delurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'delete', 'id' => $camp->id, 'sesskey' => sesskey()]);
            $stepsurl = new moodle_url('/local/trainingreminder/steps.php', ['campid' => $camp->id]);
            $testurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'test', 'id' => $camp->id, 'sesskey' => sesskey()]);
            
            $target_badge = '<span class="badge badge-info">SINGLE COURSE</span>';
            $status_badge = $camp->enabled ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Disabled</span>';
            
            $actions = html_writer::link($stepsurl, 'Manage Rules', ['class' => 'btn btn-sm btn-success mr-1']) . 
                       html_writer::link($testurl, 'Test', ['class' => 'btn btn-sm btn-outline-warning mr-1']) . 
                       html_writer::link($editurl, 'Edit', ['class' => 'btn btn-sm btn-outline-primary mr-1']) . 
                       html_writer::link($delurl, 'Delete', ['class' => 'btn btn-sm btn-outline-danger', 'onclick' => 'return confirm("Are you sure?");']);

            $table->data[] = ['<strong>' . s($camp->name) . '</strong>', $target_badge, $status_badge, $actions];
        }
        if (empty($campaigns)) { echo '<div class="alert alert-info">No reminder campaigns found.</div>'; } else { echo html_writer::table($table); }
        echo '</div></div>';
    }

    if ($tab === 'logs') {
        echo '<div class="card shadow-sm mb-4"><div class="card-body"><div class="d-flex justify-content-between align-items-center">';
        echo '<div><h5 class="mb-0">Delivery Audit Trail</h5><small class="text-muted">Showing last 50 records.</small></div>';
        echo '<a href="#" class="btn btn-warning shadow-sm font-weight-bold text-dark" onclick="alert(\'UPGRADE TO PRO: One-click CSV Audit Exports are only available in the Premium edition.\'); return false;"> Download CSV <span class="badge badge-dark ml-1">PRO</span></a>';
        echo '</div></div></div>';

        $sql = "SELECT l.id, u.firstname, u.lastname, u.email, c.fullname as coursename, camp.name as campname, l.timesent, s.step_order
                FROM {local_trainingreminder_logs} l
                JOIN {user} u ON u.id = l.userid JOIN {course} c ON c.id = l.courseid JOIN {local_trainingreminder_camps} camp ON camp.id = l.campid
                LEFT JOIN {local_trainingreminder_steps} s ON s.id = l.stepid ORDER BY l.timesent DESC";
        $logs = $DB->get_records_sql($sql, [], 0, 50);

        $logtable = new html_table();
        $logtable->attributes['class'] = 'table table-bordered table-striped bg-white shadow-sm';
        $logtable->head = ['Date Sent', 'Recipient', 'Email', 'Course', 'Rule Triggered'];
        $logtable->data = [];

        foreach ($logs as $log) {
            $date = userdate($log->timesent, get_string('strftimedatetime', 'core_langconfig'));
            $logtable->data[] = [$date, s($log->firstname . ' ' . $log->lastname), s($log->email), s($log->coursename), 'Step ' . s($log->step_order)];
        }
        if (empty($logs)) { echo '<div class="alert alert-warning">No deliveries yet.</div>'; } else { echo html_writer::table($logtable); }
    }
    
    if ($tab === 'settings') {
        $master_enable = get_config('local_trainingreminder', 'master_enable');
        if ($master_enable === false) $master_enable = 1; 

        echo '<div class="card shadow-sm mb-4" style="max-width: 800px;"><div class="card-body"><h4 class="card-title">Sitewide Preferences</h4><hr>';
        echo '<form method="POST" action="index.php"><input type="hidden" name="action" value="savesettings"><input type="hidden" name="sesskey" value="'.sesskey().'">';
        echo '<div class="form-group mb-4"><label><strong>Master Engine Switch</strong></label><br>';
        $chk = $master_enable ? 'checked' : '';
        echo '<div class="form-check"><input type="checkbox" class="form-check-input" id="master_enable" name="master_enable" value="1" '.$chk.'>';
        echo '<label class="form-check-label font-weight-bold" for="master_enable">' . ($master_enable ? 'ENGINE IS ACTIVE' : 'ENGINE IS PAUSED') . '</label></div></div>';
        echo '<button type="submit" class="btn btn-primary">Save Global Settings</button></form></div></div>';
        
        echo '<div class="card border-warning shadow-sm" style="max-width: 800px; background-color: #fff9e6;"><div class="card-body">';
        echo '<h5 class="text-warning text-dark"><i class="fa fa-star"></i> Enterprise Settings Locked</h5>';
        echo '<p class="mb-0 text-dark">Upgrade to PRO to unlock <strong>Custom Sender Email Overrides</strong> (e.g. hr@company.com), <strong>Audit Compliance BCCs</strong>, and <strong>Email Batch Throttling</strong> to protect your SMTP server.</p>';
        echo '</div></div>';
    }
}
echo $OUTPUT->footer();
