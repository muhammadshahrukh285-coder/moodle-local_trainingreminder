<?php
/**
 * Manage Automation Rules (With Clone Feature)
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_trainingreminder_manage');
$context = context_system::instance();

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$url = new moodle_url('/local/trainingreminder/manage.php');

$PAGE->set_url($url);
$PAGE->set_title('Manage Rules');
$PAGE->set_heading('Training Reminder Rules');

// 1. Handle Rule Deletion
if ($action === 'delete' && $id && confirm_sesskey()) {
    $DB->delete_records('local_trainingreminder', ['id' => $id]);
    $DB->delete_records('local_trainingreminder_logs', ['reminderid' => $id]); // Clean up associated logs
    redirect($url, 'Rule deleted successfully!', null, \core\output\notification::NOTIFY_SUCCESS);
}

// 2. Handle Rule Duplication (CLONE)
if ($action === 'clone' && $id && confirm_sesskey()) {
    $rule = $DB->get_record('local_trainingreminder', ['id' => $id], '*', MUST_EXIST);
    
    // Clear the ID and append "(Copy)" to the subject so it inserts as a brand new rule
    unset($rule->id);
    $rule->subject = $rule->subject . ' (Copy)';
    $rule->timecreated = time();
    $rule->timemodified = time();
    
    $DB->insert_record('local_trainingreminder', $rule);
    redirect($url, 'Rule duplicated successfully!', null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

echo '<div class="card"><div class="card-body">';
echo '<div class="d-flex justify-content-between align-items-center mb-4">';
echo '<h4>Active Reminders</h4>';
echo '<div>';
echo html_writer::link(new moodle_url('/local/trainingreminder/dashboard.php'), '&larr; Dashboard', ['class' => 'btn btn-outline-secondary btn-sm mr-2']);
echo html_writer::link(new moodle_url('/local/trainingreminder/edit.php'), '<i class="fa fa-plus"></i> Create New Rule', ['class' => 'btn btn-primary btn-sm']);
echo '</div></div>';

$rules = $DB->get_records('local_trainingreminder');

if (empty($rules)) {
    echo '<p class="text-muted">No automation rules have been created yet.</p>';
} else {
    $table = new html_table();
    $table->head = ['ID', 'Subject', 'Target', 'Trigger Day', 'Frequency (Nag)', 'Actions'];
    $table->attributes['class'] = 'table table-striped table-hover';
    
    foreach ($rules as $r) {
        $editurl = new moodle_url('/local/trainingreminder/edit.php', ['id' => $r->id]);
        $delurl = new moodle_url($url, ['action' => 'delete', 'id' => $r->id, 'sesskey' => sesskey()]);
        $cloneurl = new moodle_url($url, ['action' => 'clone', 'id' => $r->id, 'sesskey' => sesskey()]);
        
        // Target display formatting
        $targetinfo = ucfirst($r->targettype) . ' (IDs: ' . $r->targetids . ')';
        
        // Frequency display formatting
        $recur = (isset($r->recurring) && $r->recurring) ? 'Every ' . $r->frequencydays . ' days' : '<span class="text-muted">Once</span>';
        
        $actions = html_writer::link($editurl, '<i class="fa fa-pencil"></i> Edit', ['class' => 'btn btn-sm btn-outline-primary mr-1']);
        $actions .= html_writer::link($cloneurl, '<i class="fa fa-copy"></i> Clone', ['class' => 'btn btn-sm btn-outline-info mr-1']);
        $actions .= html_writer::link($delurl, '<i class="fa fa-trash"></i> Delete', [
            'class' => 'btn btn-sm btn-outline-danger', 
            'onclick' => 'return confirm("Are you sure you want to delete this rule? All log history for this rule will also be deleted.");'
        ]);
        
        $table->data[] = [
            $r->id,
            format_string($r->subject),
            $targetinfo,
            'Day ' . $r->reminderdays,
            $recur,
            $actions
        ];
    }
    echo html_writer::table($table);
}
echo '</div></div>';

echo $OUTPUT->footer();
