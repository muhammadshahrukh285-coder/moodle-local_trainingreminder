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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.
/**
 * Add, edit, or clone a training reminder rule.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$clone = optional_param('clone', 0, PARAM_INT);
$url = new moodle_url('/local/trainingreminder/edit.php', array_filter(['id' => $id, 'clone' => $clone]));
$context = context_system::instance();

require_login();
require_capability('local/trainingreminder:manage', $context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$title = $id ? get_string('editrule', 'local_trainingreminder') : get_string('addrule', 'local_trainingreminder');
$PAGE->set_title($title);
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));

$mform = new \local_trainingreminder\form\rule_form(null, ['id' => $id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/trainingreminder/manage.php'));
} else if ($data = $mform->get_data()) {
    $targetids = [];
    if ($data->targettype === 'categories') {
        $targetids = array_map('intval', (array)($data->categoryids ?? []));
    } else if ($data->targettype === 'courses') {
        $targetids = array_map('intval', (array)($data->courseids ?? []));
    }
    $record = (object)[
        'targettype' => $data->targettype,
        'target' => implode(',', array_filter($targetids)),
        'reminderdays' => max(0, (int)$data->reminderdays),
        'recurring' => empty($data->recurring) ? 0 : 1,
        'frequencydays' => empty($data->recurring) ? 0 : max(1, (int)$data->frequencydays),
        'subject' => clean_param($data->subject, PARAM_TEXT),
        'message' => $data->message_editor['text'] ?? '',
        'messageformat' => $data->message_editor['format'] ?? FORMAT_HTML,
        'timemodified' => time(),
        'usermodified' => $USER->id,
    ];

    if ($id) {
        $record->id = $id;
        $DB->update_record('local_trainingreminder', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_trainingreminder', $record);
    }
    redirect(new moodle_url('/local/trainingreminder/manage.php'), get_string('rulesaved', 'local_trainingreminder'),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($id || $clone) {
    $rule = $DB->get_record('local_trainingreminder', ['id' => $id ?: $clone], '*', MUST_EXIST);
    $ids = array_values(array_filter(array_map('intval', explode(',', (string)$rule->target))));
    $rule->categoryids = $rule->targettype === 'categories' ? $ids : [];
    $rule->courseids = $rule->targettype === 'courses' ? $ids : [];
    $rule->message_editor = [
        'text' => $rule->message,
        'format' => $rule->messageformat ?? FORMAT_HTML,
    ];
    if ($clone) {
        unset($rule->id);
        $rule->subject .= ' (' . get_string('copylabel', 'local_trainingreminder') . ')';
    }
    $mform->set_data($rule);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform->display();
echo $OUTPUT->footer();
