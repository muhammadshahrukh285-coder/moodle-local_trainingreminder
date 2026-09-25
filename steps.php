<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('local/trainingreminder:manage', $context);

$campid = required_param('campid', PARAM_INT);
$action = optional_param('action', 'list', PARAM_ALPHA);
$stepid = optional_param('id', 0, PARAM_INT);

$campaign = $DB->get_record('local_trainingreminder_camps', ['id' => $campid], '*', MUST_EXIST);

$PAGE->set_url(new moodle_url('/local/trainingreminder/steps.php', ['campid' => $campid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('managesteps', 'local_trainingreminder'));
$PAGE->set_heading($campaign->name . ' - ' . get_string('managesteps', 'local_trainingreminder') . ' (Free Edition)');
$PAGE->set_pagelayout('admin');

// Handle Form Submission for Rules
if ($action === 'save' && confirm_sesskey()) {
    
    // HARD LOCK: Prevent POST injection bypass for the 2-rule limit
    if ($stepid == 0) {
        $current_count = $DB->count_records('local_trainingreminder_steps', ['campid' => $campid]);
        if ($current_count >= 2) {
            redirect(new moodle_url('/local/trainingreminder/steps.php', ['campid' => $campid]), 'Free Version Limit: You cannot create more than 2 rules.', null, \core\output\notification::NOTIFY_ERROR);
        }
    }
    
    $step_order = required_param('step_order', PARAM_INT);
    $delay_days = required_param('delay_days', PARAM_INT);
    $subject = required_param('subject', PARAM_TEXT);
    $body_template = required_param('body_template', PARAM_RAW);

    $record = new stdClass();
    $record->campid = $campid;
    $record->step_order = $step_order;
    $record->delay_days = $delay_days;
    $record->subject = $subject;
    $record->body_template = $body_template;
    $record->timemodified = time();

    if ($stepid > 0) {
        $record->id = $stepid;
        $DB->update_record('local_trainingreminder_steps', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_trainingreminder_steps', $record);
    }

    redirect(new moodle_url('/local/trainingreminder/steps.php', ['campid' => $campid]), 'Rule saved successfully!', null, \core\output\notification::NOTIFY_SUCCESS);
}

// Handle Rule Deletion
if ($action === 'delete' && $stepid > 0 && confirm_sesskey()) {
    $DB->delete_records('local_trainingreminder_logs', ['stepid' => $stepid]);
    $DB->delete_records('local_trainingreminder_steps', ['id' => $stepid]);
    redirect(new moodle_url('/local/trainingreminder/steps.php', ['campid' => $campid]), 'Rule deleted.', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

// ==========================================
// VIEW 1: ADD / EDIT RULE FORM
// ==========================================
if ($action === 'edit' || $action === 'add') {
    $step = null;
    if ($stepid > 0) {
        $step = $DB->get_record('local_trainingreminder_steps', ['id' => $stepid]);
    }
    
    $step_order = $step ? $step->step_order : 1;
    $delay_days = $step ? $step->delay_days : 7;
    $subject = $step ? $step->subject : 'Reminder: Please complete {{coursename}}';
    
    $default_template = '
<div style="font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 30px 10px;">
<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <div style="background-color: #0056a4; color: #ffffff; padding: 25px; text-align: center;">
        <h2 style="margin: 0; font-size: 24px; letter-spacing: 1px;">TRAINING REMINDER</h2>
    </div>
    <div style="padding: 30px; color: #444444; line-height: 1.6;">
        <p style="font-size: 16px; margin-top: 0;">Hello <strong>{{firstname}}</strong>,</p>
        <p style="font-size: 16px;">This is an automated courtesy reminder regarding your pending training requirements. Please prioritize completing your assigned modules.</p>
        
        <div style="background-color: #fff8e6; border-left: 4px solid #ffc107; padding: 15px 20px; margin: 25px 0;">
            <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 18px;">Action Required</h3>
            <div style="color: #333; font-weight: 500;">Please log in to complete: <strong>{{coursename}}</strong></div>
        </div>
        
        <div style="text-align: center; margin-top: 35px; margin-bottom: 15px;">
            <a href="{{siteurl}}" style="background-color: #0056a4; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 4px; font-size: 16px; font-weight: bold; display: inline-block;">Log in to the LMS</a>
        </div>
    </div>
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee;">
        This is an automated message from the Learning & Development team.<br>Please do not reply directly to this email.
    </div>
</div>
</div>';

    $body_val = $step ? $step->body_template : $default_template;

    $formaction = new moodle_url('/local/trainingreminder/steps.php', ['action' => 'save', 'campid' => $campid, 'id' => $stepid]);
    $backurl = new moodle_url('/local/trainingreminder/steps.php', ['campid' => $campid]);

    echo html_writer::start_tag('div', ['class' => 'container-fluid']);
    echo html_writer::link($backurl, '&larr; Back to Rules', ['class' => 'btn btn-secondary mb-3']);
    
    echo html_writer::start_tag('form', ['method' => 'POST', 'action' => $formaction]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo '<div class="card shadow-sm"><div class="card-body">';
    
    echo '<div class="form-group mb-3">';
    echo '<label><strong>Step Order</strong> (e.g., 1 for first reminder, 2 for second)</label>';
    echo '<input type="number" class="form-control" name="step_order" value="' . s($step_order) . '" required>';
    echo '</div>';

    echo '<div class="form-group mb-3">';
    echo '<label><strong>Delay</strong> (Days after Enrolment)</label>';
    echo '<input type="number" class="form-control" name="delay_days" value="' . s($delay_days) . '" required>';
    echo '</div>';

    echo '<div class="form-group mb-3">';
    echo '<label><strong>Email Subject</strong></label>';
    echo '<input type="text" class="form-control" name="subject" value="' . s($subject) . '" required>';
    echo '</div>';

    echo '<div class="form-group mb-3">';
    echo '<label><strong>' . get_string('bodytemplate', 'local_trainingreminder') . '</strong></label>';
    echo '<textarea class="form-control" name="body_template" rows="18" required>' . s($body_val) . '</textarea>';
    echo '<small class="form-text text-muted">Available tags: {{firstname}}, {{lastname}}, {{coursename}}, {{siteurl}}.</small>';
    echo '</div>';

    echo '<button type="submit" class="btn btn-primary btn-lg mt-2">' . get_string('savechanges', 'local_trainingreminder') . '</button>';
    echo '</div></div>';
    echo html_writer::end_tag('form');
    echo html_writer::end_tag('div');

} 
// ==========================================
// VIEW 2: LIST OF RULES (WITH PRO LOCK)
// ==========================================
else {
    $backurl = new moodle_url('/local/trainingreminder/index.php');
    echo html_writer::link($backurl, '&larr; Back to Campaigns', ['class' => 'btn btn-secondary mb-3 mr-2']);

    $total_rules = $DB->count_records('local_trainingreminder_steps', ['campid' => $campid]);

    // --- THE FREE VERSION LOCK LOGIC ---
    if ($total_rules >= 2) {
        echo '<a href="#" class="btn btn-warning mb-3 font-weight-bold text-dark shadow-sm" onclick="alert(\'FREE VERSION LIMIT REACHED: You are limited to 2 rules per campaign. Upgrade to Training Reminder PRO to build unlimited escalation ladders and infinite loops!\'); return false;">Add New Reminder Rule <span class="badge badge-dark ml-1">PRO</span></a>';
    } else {
        $addurl = new moodle_url('/local/trainingreminder/steps.php', ['action' => 'add', 'campid' => $campid]);
        echo html_writer::link($addurl, '+ Add New Reminder Rule', ['class' => 'btn btn-primary mb-3 shadow-sm']);
    }

    $steps = $DB->get_records('local_trainingreminder_steps', ['campid' => $campid], 'step_order ASC');

    $table = new html_table();
    $table->attributes['class'] = 'table table-bordered table-hover bg-white shadow-sm mt-2';
    $table->head = ['Step Order', 'Trigger Delay', 'Email Subject', 'Actions'];
    $table->data = [];

    foreach ($steps as $step) {
        $editurl = new moodle_url('/local/trainingreminder/steps.php', ['action' => 'edit', 'campid' => $campid, 'id' => $step->id]);
        $delurl = new moodle_url('/local/trainingreminder/steps.php', ['action' => 'delete', 'campid' => $campid, 'id' => $step->id, 'sesskey' => sesskey()]);
        
        $actions = html_writer::link($editurl, 'Edit', ['class' => 'btn btn-sm btn-info mr-2']) . ' ' .
                   html_writer::link($delurl, 'Delete', ['class' => 'btn btn-sm btn-outline-danger', 'onclick' => 'return confirm("Are you sure you want to delete this rule?");']);

        $table->data[] = [
            '<strong>Step ' . s($step->step_order) . '</strong>',
            s($step->delay_days) . ' days after enrol',
            s($step->subject),
            $actions
        ];
    }

    if (empty($steps)) {
        echo '<div class="alert alert-info shadow-sm">No reminder rules have been created for this campaign yet.</div>';
    } else {
        echo html_writer::table($table);
    }
}

echo $OUTPUT->footer();
