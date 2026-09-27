<?php
/**
 * Executive Analytics & Settings Dashboard (With Manager CC Field)
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Security setup.
admin_externalpage_setup('local_trainingreminder_dashboard');
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/local/trainingreminder/dashboard.php');
$PAGE->set_url($url);
$PAGE->set_title('Executive Dashboard');
$PAGE->set_heading('Training Reminder Automation');

// 1. Process Settings Save.
if (optional_param('savesettings', 0, PARAM_INT) && confirm_sesskey()) {
    $logourl = optional_param('logourl', '', PARAM_URL);
    $brandcolor = optional_param('brandcolor', '#0f6cbf', PARAM_TEXT);
    $managerfield = optional_param('managerfield', 'line_manager_email', PARAM_ALPHANUMEXT);
    
    set_config('logourl', $logourl, 'local_trainingreminder');
    set_config('brandcolor', $brandcolor, 'local_trainingreminder');
    set_config('managerfield', $managerfield, 'local_trainingreminder');
    redirect($url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// 2. Process Send Test Email.
if (optional_param('sendtest', 0, PARAM_INT) && confirm_sesskey()) {
    global $USER;
    $logourl = get_config('local_trainingreminder', 'logourl');
    $brandcolor = get_config('local_trainingreminder', 'brandcolor') ?: '#0f6cbf';
    $siteurl = $CFG->wwwroot;

    $logohtml = '';
    if (!empty($logourl)) {
        $logohtml = '<div style="text-align: center; padding: 20px 0 0 0;"><img src="'.s($logourl).'" alt="Logo" style="max-width: 220px; height: auto;"></div>';
    }

    $message = "<p>Hi " . $USER->firstname . ",</p><p>This is a test preview of your Training Reminder email template. If you can see this, your custom colors and logo are working perfectly!</p>";
    $messagetext = "Hi " . $USER->firstname . ",\n\nThis is a test preview of your Training Reminder email template.";

    $premium_html_message = '
    <div style="background-color: #f4f6f9; padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="background-color: '.s($brandcolor).'; height: 6px; width: 100%;"></div>
            ' . $logohtml . '
            <div style="padding: 30px 40px; color: #444444; line-height: 1.6; font-size: 15px;">
                ' . $message . '
            </div>
            <div style="text-align: center; padding: 0 40px 40px;">
                <a href="' . s($siteurl) . '/my/" style="display: inline-block; background-color: '.s($brandcolor).'; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; font-size: 16px;">' . get_string('gotodashboard', 'local_trainingreminder') . '</a>
            </div>
        </div>
    </div>';

    email_to_user($USER, $USER, 'Test Preview: Training Reminder', $messagetext, $premium_html_message);
    redirect($url, 'Test email sent to ' . $USER->email, null, \core\output\notification::NOTIFY_SUCCESS);
}

$currentlogo = get_config('local_trainingreminder', 'logourl');
$currentcolor = get_config('local_trainingreminder', 'brandcolor') ?: '#0f6cbf';
$currentmanagerfield = get_config('local_trainingreminder', 'managerfield');
if ($currentmanagerfield === false) { $currentmanagerfield = 'line_manager_email'; } // Default

// 3. Fetch Executive Metrics.
$totalrules = $DB->count_records('local_trainingreminder');
$totalsent = $DB->count_records('local_trainingreminder_logs');
$totalsuccess = $DB->count_records('local_trainingreminder_logs', ['status' => 1]);
$successrate = $totalsent > 0 ? round(($totalsuccess / $totalsent) * 100) : 100;
$weekago = time() - (7 * DAYSECS);
$sentthisweek = $DB->count_records_select('local_trainingreminder_logs', 'timesent >= ?', [$weekago]);

// 4. Prepare Data for Line Chart.
$dailylabels = [];
$dailycounts = array_fill(0, 7, 0);
for ($i = 6; $i >= 0; $i--) {
    $dailylabels[] = date('M d', strtotime("-$i days"));
}
$logs7days = $DB->get_records_select('local_trainingreminder_logs', 'timesent >= ?', [$weekago], '', 'id, timesent');
foreach ($logs7days as $l) {
    $logdate = date('M d', $l->timesent);
    $idx = array_search($logdate, $dailylabels);
    if ($idx !== false) {
        $dailycounts[$idx]++;
    }
}

// 5. Fetch Top Rules.
$sql = "SELECT r.id, r.subject, COUNT(l.id) as logcount
          FROM {local_trainingreminder_logs} l
          JOIN {local_trainingreminder} r ON r.id = l.reminderid
      GROUP BY r.id, r.subject
      ORDER BY logcount DESC";
$rulecounts = $DB->get_records_sql($sql, null, 0, 5);

$chart_line = new \core\chart_line();
$series_line = new \core\chart_series('Reminders Sent', $dailycounts);
$series_line->set_color('#0f6cbf');
$chart_line->add_series($series_line);
$chart_line->set_labels($dailylabels);

// 6. Fetch Latest 5 Audit Logs.
$sql_logs = "SELECT l.id, l.timesent, l.status, u.firstname, u.lastname, c.fullname AS coursename, r.subject
               FROM {local_trainingreminder_logs} l
               JOIN {user} u ON u.id = l.userid
               JOIN {course} c ON c.id = l.courseid
               JOIN {local_trainingreminder} r ON r.id = l.reminderid
           ORDER BY l.timesent DESC";
$logs = $DB->get_records_sql($sql_logs, null, 0, 5);

// ---------------- RENDER UI ----------------
echo $OUTPUT->header();

echo '
<style>
    .pr-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 20px; border: 1px solid #e5e7eb; margin-bottom: 20px; }
    .pr-metric { font-size: 2.2rem; font-weight: bold; color: #111827; line-height: 1.2; margin-bottom: 5px; }
    .pr-metric-label { font-size: 0.85rem; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
    .pr-header { font-size: 1.1rem; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; color: #374151; }
    .pr-badge-success { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; white-space: nowrap; }
    .pr-badge-error { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; white-space: nowrap; }
    .pr-progress-wrap { margin-bottom: 18px; }
    .pr-progress-label { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; font-weight: 500; color: #374151; }
    .pr-progress-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%; }
    .pr-progress { height: 8px; background-color: #f3f4f6; border-radius: 4px; overflow: hidden; width: 100%; }
    .pr-progress-bar { height: 100%; border-radius: 4px; }
</style>
';

// ROW 1: KPI Metrics
echo '<div class="row">';
echo '<div class="col-12 col-sm-6 col-xl-3"><div class="pr-card text-center h-100"><div class="pr-metric text-primary">' . $totalsent . '</div><div class="pr-metric-label">Total Sent</div></div></div>';
echo '<div class="col-12 col-sm-6 col-xl-3"><div class="pr-card text-center h-100"><div class="pr-metric">' . $sentthisweek . '</div><div class="pr-metric-label">Last 7 Days</div></div></div>';
echo '<div class="col-12 col-sm-6 col-xl-3"><div class="pr-card text-center h-100"><div class="pr-metric text-success">' . $successrate . '%</div><div class="pr-metric-label">Delivery Rate</div></div></div>';
echo '<div class="col-12 col-sm-6 col-xl-3"><div class="pr-card text-center h-100"><div class="pr-metric text-info">' . $totalrules . '</div><div class="pr-metric-label">Active Rules</div></div></div>';
echo '</div>';

// ROW 2: Line Chart (Left) + Widgets (Right)
echo '<div class="row">';
echo '<div class="col-12 col-lg-8"><div class="pr-card h-100"><div class="pr-header">Email Volume (Last 7 Days)</div><div>' . $OUTPUT->render($chart_line) . '</div></div></div>';

echo '<div class="col-12 col-lg-4">';

// Top Rules
echo '<div class="pr-card mb-4"><div class="pr-header">Top Rules by Volume</div>';
if (!empty($rulecounts) && $totalsent > 0) {
    $colors = ['#0f6cbf', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899'];
    $i = 0;
    foreach ($rulecounts as $rc) {
        $percent = round(($rc->logcount / $totalsent) * 100);
        echo '<div class="pr-progress-wrap"><div class="pr-progress-label"><span class="pr-progress-title">' . s($rc->subject) . '</span><span>' . $rc->logcount . ' (' . $percent . '%)</span></div><div class="pr-progress"><div class="pr-progress-bar" style="width: ' . $percent . '%; background-color: ' . $colors[$i % count($colors)] . ';"></div></div></div>';
        $i++;
    }
} else {
    echo '<p class="text-muted small text-center mt-2 mb-2">No rules triggered yet.</p>';
}
echo '</div>'; 

// Plugin Settings (Now includes Manager Field)
echo '<div class="pr-card mb-4"><div class="pr-header">Plugin Settings</div>';
echo '<form method="post" action="' . $url . '"><input type="hidden" name="sesskey" value="' . sesskey() . '"><input type="hidden" name="savesettings" value="1">';
echo '<div class="form-group mb-3"><label class="font-weight-bold" style="font-size:0.9rem;">Logo URL</label><input type="url" name="logourl" class="form-control form-control-sm" value="' . s($currentlogo) . '"></div>';
echo '<div class="form-group mb-3"><label class="font-weight-bold" style="font-size:0.9rem;">Brand Color</label><div class="d-flex align-items-center"><input type="color" name="brandcolor" class="form-control p-1" style="width: 40px; height: 32px;" value="' . s($currentcolor) . '"></div></div>';
echo '<div class="form-group mb-3"><label class="font-weight-bold" style="font-size:0.9rem;">Manager Profile Field</label><input type="text" name="managerfield" class="form-control form-control-sm" placeholder="line_manager_email" value="' . s($currentmanagerfield) . '"><small class="text-muted">Profile field shortname to CC.</small></div>';
echo '<button type="submit" class="btn btn-primary btn-sm w-100 mb-2">Save Settings</button></form>';
echo '<form method="post" action="' . $url . '"><input type="hidden" name="sesskey" value="' . sesskey() . '"><input type="hidden" name="sendtest" value="1"><button type="submit" class="btn btn-outline-secondary btn-sm w-100">Send Test Email</button></form></div>';

// Quick Actions
echo '<div class="pr-card" style="background-color: #f8fafc; border-color: #cbd5e1;">';
echo '<div class="pr-header border-0 pb-0 mb-3 text-dark">Plugin Administration</div>';
echo html_writer::link(new moodle_url('/local/trainingreminder/manage.php'), 'Configure Automation Rules &rarr;', ['class' => 'btn btn-success btn-sm w-100 mb-3 font-weight-bold', 'style' => 'font-size: 0.95rem; padding: 8px;']);

$taskurl = new moodle_url('/admin/tool/task/scheduledtasks.php', ['action' => 'edit', 'task' => '\local_trainingreminder\task\send_reminders']);
echo html_writer::link($taskurl, '<i class="fa fa-clock-o"></i> Edit Scheduled Task Timing', ['class' => 'btn btn-outline-dark btn-sm w-100']);
echo '</div>';

echo '</div></div>';

// ROW 3: Audit Logs
echo '<div class="row"><div class="col-12"><div class="pr-card">';
echo '<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">';
echo '<div class="pr-header mb-0 border-0 pb-0">Recent Deliveries (Last 5)</div>';
echo html_writer::link(new moodle_url('/local/trainingreminder/logs.php'), 'View Full Audit Log & Export &rarr;', ['class' => 'btn btn-primary btn-sm']);
echo '</div>';

if (empty($logs)) {
    echo '<p class="text-muted text-center my-4">No audit logs found.</p>';
} else {
    echo '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Time</th><th>User</th><th>Course</th><th>Triggered Rule</th><th>Status</th></tr></thead><tbody>';
    foreach ($logs as $log) {
        $statusbadge = $log->status ? '<span class="pr-badge-success">OK</span>' : '<span class="pr-badge-error">FAIL</span>';
        echo '<tr><td>' . userdate($log->timesent, '%b %d, %Y - %H:%M') . '</td><td>' . format_string($log->firstname . ' ' . $log->lastname) . '</td><td>' . format_string($log->coursename) . '</td><td>' . format_string($log->subject) . '</td><td>' . $statusbadge . '</td></tr>';
    }
    echo '</tbody></table></div>';
}
echo '</div></div></div>';

echo $OUTPUT->footer();
