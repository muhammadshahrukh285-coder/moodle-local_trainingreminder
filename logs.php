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
 * Reminder delivery audit log and export.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_trainingreminder_logs');
$context = context_system::instance();
require_capability('local/trainingreminder:manage', $context);
$url = new moodle_url('/local/trainingreminder/logs.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('auditlogs', 'local_trainingreminder'));
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));

$download = optional_param('download', '', PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);
$where = '';
$params = [];
if ($search !== '') {
    $like = '%' . $DB->sql_like_escape($search) . '%';
    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $where = ' WHERE (' . $DB->sql_like($fullname, '?', false) . '
        OR ' . $DB->sql_like('u.email', '?', false) . '
        OR ' . $DB->sql_like('c.fullname', '?', false) . '
        OR ' . $DB->sql_like('r.subject', '?', false) . ')';
    $params = [$like, $like, $like, $like];
}
$select = "SELECT l.id, l.timesent, l.status, l.reminderid, l.userid, l.courseid,
                  u.firstname, u.lastname, u.email, c.fullname AS coursename, r.subject
             FROM {local_trainingreminder_logs} l
             JOIN {user} u ON u.id = l.userid
             JOIN {course} c ON c.id = l.courseid
             LEFT JOIN {local_trainingreminder} r ON r.id = l.reminderid
             $where";

if ($download) {
    $rs = $DB->get_recordset_sql($select . ' ORDER BY l.timesent DESC', $params);
    $columns = [
        get_string('logid', 'local_trainingreminder'), get_string('timesent', 'local_trainingreminder'),
        get_string('firstname'), get_string('lastname'), get_string('email'),
        get_string('course'), get_string('ruletriggered', 'local_trainingreminder'),
        get_string('deliverystatus', 'local_trainingreminder'),
    ];
    \core\dataformat::download_data('training_reminder_audit_' . date('Ymd_Hi'), $download, $columns,
        $rs, function($log) {
            return [
                $log->id, userdate($log->timesent, '%Y-%m-%d %H:%M:%S'), $log->firstname,
                $log->lastname, $log->email, $log->coursename, $log->subject,
                $log->status ? get_string('delivered', 'local_trainingreminder') :
                    get_string('failed', 'local_trainingreminder'),
            ];
        });
    $rs->close();
    exit;
}

$logs = $DB->get_records_sql($select . ' ORDER BY l.timesent DESC', $params, 0, 500);
$rows = [];
foreach ($logs as $log) {
    $rows[] = [
        'time' => userdate($log->timesent, get_string('strftimedatetime', 'langconfig')),
        'recipient' => fullname((object)['firstname' => $log->firstname, 'lastname' => $log->lastname]),
        'course' => format_string($log->coursename),
        'subject' => format_string($log->subject ?? ''),
        'status' => $log->status ? get_string('delivered', 'local_trainingreminder') :
            get_string('failed', 'local_trainingreminder'),
        'success' => !empty($log->status),
    ];
}
$data = [
    'rows' => $rows, 'haslogs' => !empty($rows), 'search' => s($search), 'hassearch' => $search !== '',
    'searchurl' => $url->out(false),
    'csvurl' => (new moodle_url($url, ['download' => 'csv', 'search' => $search]))->out(false),
    'excelurl' => (new moodle_url($url, ['download' => 'excel', 'search' => $search]))->out(false),
    'dashboardurl' => (new moodle_url('/local/trainingreminder/dashboard.php'))->out(false),
    'showlimit' => !$search,
    'str' => [
        'history' => get_string('deliveryhistory', 'local_trainingreminder'),
        'dashboard' => get_string('dashboard', 'local_trainingreminder'),
        'searchplaceholder' => get_string('logsearchplaceholder', 'local_trainingreminder'),
        'search' => get_string('search'),
        'clear' => get_string('clear'),
        'auditprompt' => get_string('auditprompt', 'local_trainingreminder'),
        'csv' => get_string('downloadcsv', 'local_trainingreminder'),
        'excel' => get_string('downloadexcel', 'local_trainingreminder'),
        'time' => get_string('time'),
        'recipient' => get_string('recipient', 'local_trainingreminder'),
        'course' => get_string('course'),
        'rule' => get_string('ruletriggered', 'local_trainingreminder'),
        'status' => get_string('status'),
        'empty' => get_string('nologsfound', 'local_trainingreminder'),
        'nomatch' => get_string('nologsmatch', 'local_trainingreminder'),
        'limit' => get_string('loglimit', 'local_trainingreminder'),
        'filtered' => get_string('logfiltered', 'local_trainingreminder'),
    ],
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_trainingreminder/logs', $data);
echo $OUTPUT->footer();
