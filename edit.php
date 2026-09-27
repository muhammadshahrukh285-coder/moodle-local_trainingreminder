<?php
/**
 * Page to add, edit, or clone a training reminder rule.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$clone = optional_param('clone', 0, PARAM_INT);

// Set up the page.
$url = new moodle_url('/local/trainingreminder/edit.php');
if ($id) {
    $url->param('id', $id);
}
if ($clone) {
    $url->param('clone', $clone);
}

$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

// Security checks.
require_login();
require_capability('local/trainingreminder:manage', context_system::instance());

// Determine page title.
$strtitle = get_string('addrule', 'local_trainingreminder');
if ($id) {
    $strtitle = get_string('editrule', 'local_trainingreminder');
}

$PAGE->set_title($strtitle);
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));

// Instantiate the form.
$customdata = ['id' => $id];
$mform = new \local_trainingreminder\form\rule_form(null, $customdata);

// Handle form cancellation.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/trainingreminder/manage.php'));
} 
// Handle form submission.
else if ($fromform = $mform->get_data()) {
    $record = new stdClass();
    
    // Safely handle conditionally hidden fields (hideIf) which Moodle omits from POST data
    $record->targettype = isset($fromform->targettype) ? $fromform->targettype : 'all';
    $record->target = isset($fromform->target) ? $fromform->target : '';
    $record->reminderdays = isset($fromform->reminderdays) ? $fromform->reminderdays : 0;
    $record->recurring = !empty($fromform->recurring) ? 1 : 0;
    $record->frequencydays = isset($fromform->frequencydays) ? $fromform->frequencydays : 0;
    $record->subject = isset($fromform->subject) ? $fromform->subject : '';

    // Safely handle the editor array to prevent NULL crashes
    if (isset($fromform->message)) {
        $record->message = is_array($fromform->message) ? $fromform->message['text'] : $fromform->message;
    } else {
        $record->message = '';
    }
    
    $record->timemodified = time();

    if ($id) {
        $record->id = $id;
        $DB->update_record('local_trainingreminder', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_trainingreminder', $record);
    }
    
    redirect(new moodle_url('/local/trainingreminder/manage.php'), 'Rule saved successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
}

// Populate the form if we are editing or cloning an existing rule.
if ($id || $clone) {
    $loadid = $id ? $id : $clone;
    $rule = $DB->get_record('local_trainingreminder', ['id' => $loadid], '*', MUST_EXIST);
    
    // Prepare the HTML editor format safely
    $msgtext = isset($rule->message) ? $rule->message : '';
    $rule->message = ['text' => $msgtext, 'format' => FORMAT_HTML];
    
    // If cloning, clear the ID so it saves as a new record, and append a note to the subject.
    if ($clone) {
        unset($rule->id);
        $rule->subject = $rule->subject . ' (Copy)';
    }
    
    $mform->set_data($rule);
}

// Output the page.
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$mform->display();
echo $OUTPUT->footer();
