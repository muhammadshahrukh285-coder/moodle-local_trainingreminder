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
 * Manage reminder rules.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_trainingreminder_manage');
$context = context_system::instance();
require_capability('local/trainingreminder:manage', $context);

$url = new moodle_url('/local/trainingreminder/manage.php');
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$PAGE->set_url($url);
$PAGE->set_title(get_string('managerules', 'local_trainingreminder'));
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));

if ($action && $id && confirm_sesskey()) {
    $rule = $DB->get_record('local_trainingreminder', ['id' => $id], '*', MUST_EXIST);
    if ($action === 'delete') {
        $DB->delete_records('local_trainingreminder_logs', ['reminderid' => $id]);
        $DB->delete_records('local_trainingreminder', ['id' => $id]);
        redirect($url, get_string('ruledeleted', 'local_trainingreminder'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    } else if ($action === 'clone') {
        unset($rule->id);
        $rule->subject .= ' (' . get_string('copylabel', 'local_trainingreminder') . ')';
        $rule->timecreated = time();
        $rule->timemodified = time();
        $rule->usermodified = $USER->id;
        $DB->insert_record('local_trainingreminder', $rule);
        redirect($url, get_string('ruleduplicated', 'local_trainingreminder'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }
}

$items = [];
$rules = $DB->get_records('local_trainingreminder', null, 'id DESC');
foreach ($rules as $rule) {
    $targetlabel = get_string('targettype_' . $rule->targettype, 'local_trainingreminder');
    $target = $targetlabel;
    if ($rule->targettype !== 'all' && !empty($rule->target)) {
        $target .= ' (' . s($rule->target) . ')';
    }
    $items[] = [
        'id' => $rule->id,
        'subject' => format_string($rule->subject),
        'target' => $target,
        'days' => (int)$rule->reminderdays,
        'frequency' => empty($rule->recurring) ? get_string('once', 'local_trainingreminder') :
            get_string('everyxdays', 'local_trainingreminder', (int)$rule->frequencydays),
        'editurl' => (new moodle_url('/local/trainingreminder/edit.php', ['id' => $rule->id]))->out(false),
        'cloneurl' => (new moodle_url($url, ['action' => 'clone', 'id' => $rule->id, 'sesskey' => sesskey()]))->out(false),
        'deleteurl' => (new moodle_url($url, ['action' => 'delete', 'id' => $rule->id, 'sesskey' => sesskey()]))->out(false),
    ];
}
$data = [
    'rules' => $items,
    'hasrules' => !empty($items),
    'addurl' => (new moodle_url('/local/trainingreminder/edit.php'))->out(false),
    'dashboardurl' => (new moodle_url('/local/trainingreminder/dashboard.php'))->out(false),
    'str' => [
        'id' => get_string('id'),
        'active' => get_string('activereminders', 'local_trainingreminder'),
        'add' => get_string('createnewrule', 'local_trainingreminder'),
        'dashboard' => get_string('dashboard', 'local_trainingreminder'),
        'empty' => get_string('norules', 'local_trainingreminder'),
        'subject' => get_string('subject', 'local_trainingreminder'),
        'target' => get_string('target', 'local_trainingreminder'),
        'trigger' => get_string('triggerday', 'local_trainingreminder'),
        'frequency' => get_string('frequency', 'local_trainingreminder'),
        'actions' => get_string('actions', 'local_trainingreminder'),
        'edit' => get_string('edit'),
        'clone' => get_string('clone', 'local_trainingreminder'),
        'delete' => get_string('delete'),
        'confirm' => get_string('confirmdelete', 'local_trainingreminder'),
    ],
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_trainingreminder/manage', $data);
echo $OUTPUT->footer();
