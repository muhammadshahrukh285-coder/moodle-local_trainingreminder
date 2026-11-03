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
 * Executive analytics dashboard for training reminders.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_trainingreminder_dashboard');
$context = context_system::instance();
require_capability('local/trainingreminder:manage', $context);
$url = new moodle_url('/local/trainingreminder/dashboard.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('dashboard', 'local_trainingreminder'));
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));

if (optional_param('sendtest', 0, PARAM_INT) && confirm_sesskey()) {
    $logourl = get_config('local_trainingreminder', 'logourl');
    $brandcolor = get_config('local_trainingreminder', 'brandcolor') ?: '#0f6cbf';
    $message = get_string('testemailbody', 'local_trainingreminder', fullname($USER));
    $html = '<div style="font-family:Arial,sans-serif;padding:30px;border-top:6px solid ' . s($brandcolor) . ';">';
    if ($logourl) {
        $html .= '<p><img src="' . s($logourl) . '" alt="' . s(get_string('companylogo', 'local_trainingreminder')) .
            '" style="max-width:220px;height:auto;"></p>';
    }
    $html .= '<p>' . format_text($message, FORMAT_PLAIN) . '</p></div>';
    email_to_user($USER, $USER, get_string('testemailsubject', 'local_trainingreminder'),
        $message, $html);
    redirect($url, get_string('testemailsent', 'local_trainingreminder', $USER->email), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

$totalrules = $DB->count_records('local_trainingreminder');
$totalsent = $DB->count_records('local_trainingreminder_logs');
$totalsuccess = $DB->count_records('local_trainingreminder_logs', ['status' => 1]);
$successrate = $totalsent ? round(($totalsuccess / $totalsent) * 100) : 100;
$weekago = time() - 7 * DAYSECS;
$sentthisweek = $DB->count_records_select('local_trainingreminder_logs', 'timesent >= ?', [$weekago]);

$labels = [];
$counts = array_fill(0, 7, 0);
for ($i = 6; $i >= 0; $i--) {
    $labels[] = userdate(strtotime('-' . $i . ' days'), '%b %d');
}
$logs7days = $DB->get_records_select('local_trainingreminder_logs', 'timesent >= ?', [$weekago], '',
    'id, timesent');
foreach ($logs7days as $log) {
    $label = userdate($log->timesent, '%b %d');
    $index = array_search($label, $labels);
    if ($index !== false) {
        $counts[$index]++;
    }
}
$chart = new \core\chart_line();
$series = new \core\chart_series(get_string('reminderssent', 'local_trainingreminder'), $counts);
$chart->add_series($series);
$chart->set_labels($labels);

$sql = "SELECT r.id, r.subject, COUNT(l.id) AS logcount
          FROM {local_trainingreminder_logs} l
          JOIN {local_trainingreminder} r ON r.id = l.reminderid
      GROUP BY r.id, r.subject ORDER BY logcount DESC";
$rulecounts = $DB->get_records_sql($sql, null, 0, 5);
$toprules = [];
foreach ($rulecounts as $rule) {
    $toprules[] = ['subject' => format_string($rule->subject), 'count' => (int)$rule->logcount,
        'percent' => $totalsent ? round(($rule->logcount / $totalsent) * 100) : 0];
}
$sql = "SELECT l.timesent, l.status, u.firstname, u.lastname, c.fullname AS coursename, r.subject
          FROM {local_trainingreminder_logs} l
          JOIN {user} u ON u.id = l.userid
          JOIN {course} c ON c.id = l.courseid
          LEFT JOIN {local_trainingreminder} r ON r.id = l.reminderid
      ORDER BY l.timesent DESC";
$recentlogs = [];
foreach ($DB->get_records_sql($sql, null, 0, 5) as $log) {
    $recentlogs[] = [
        'time' => userdate($log->timesent, '%b %d, %Y - %H:%M'),
        'user' => fullname($log),
        'course' => format_string($log->coursename),
        'subject' => format_string($log->subject ?? ''),
        'status' => $log->status ? get_string('delivered', 'local_trainingreminder') :
            get_string('failed', 'local_trainingreminder'),
        'success' => !empty($log->status),
    ];
}

$data = [
    'totalsent' => $totalsent, 'sentthisweek' => $sentthisweek, 'successrate' => $successrate,
    'totalrules' => $totalrules, 'chart' => $OUTPUT->render($chart),
    'toprules' => $toprules, 'hastoprules' => !empty($toprules),
    'logs' => $recentlogs, 'haslogs' => !empty($recentlogs),
    'settingsurl' => (new moodle_url('/admin/settings.php', ['section' => 'local_trainingreminder_settings']))->out(false),
    'manageurl' => (new moodle_url('/local/trainingreminder/manage.php'))->out(false),
    'logsurl' => (new moodle_url('/local/trainingreminder/logs.php'))->out(false),
    'taskurl' => (new moodle_url('/admin/tool/task/scheduledtasks.php',
        ['action' => 'edit', 'task' => '\local_trainingreminder\task\send_reminders']))->out(false),
    'testurl' => $url->out(false), 'sesskey' => sesskey(),
    'str' => [
        'totalsent' => get_string('totalsent', 'local_trainingreminder'),
        'last7days' => get_string('last7days', 'local_trainingreminder'),
        'deliveryrate' => get_string('deliveryrate', 'local_trainingreminder'),
        'activerules' => get_string('activerules', 'local_trainingreminder'),
        'volume' => get_string('emailvolume', 'local_trainingreminder'),
        'toprules' => get_string('toprules', 'local_trainingreminder'),
        'norules' => get_string('notriggered', 'local_trainingreminder'),
        'settings' => get_string('pluginsettings', 'local_trainingreminder'),
        'configure' => get_string('configure', 'local_trainingreminder'),
        'testemail' => get_string('sendtestemail', 'local_trainingreminder'),
        'administration' => get_string('pluginadministration', 'local_trainingreminder'),
        'manage' => get_string('configurerules', 'local_trainingreminder'),
        'tasktiming' => get_string('edittasktiming', 'local_trainingreminder'),
        'recent' => get_string('recentdeliveries', 'local_trainingreminder'),
        'viewlogs' => get_string('viewauditlog', 'local_trainingreminder'),
        'nologs' => get_string('nologsfound', 'local_trainingreminder'),
        'time' => get_string('time'), 'user' => get_string('user'), 'course' => get_string('course'),
        'rule' => get_string('ruletriggered', 'local_trainingreminder'),
        'status' => get_string('status'),
    ],
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_trainingreminder/dashboard', $data);
echo $OUTPUT->footer();
