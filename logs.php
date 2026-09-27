<?php
/**
 * Full Audit Log & Export Page (With Search)
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

$url = new moodle_url('/local/trainingreminder/logs.php');
$PAGE->set_url($url);
$PAGE->set_title('Full Audit Log');
$PAGE->set_heading('Training Reminder Audit Log');

// 1. Retrieve Parameters
$download = optional_param('download', '', PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);

// 2. Build the Search Query Constraints
$where = "";
$params = [];

if (!empty($search)) {
    // Safely format the search string for SQL LIKE queries
    $searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    
    $where = " WHERE " . $DB->sql_like($fullname, '?', false) . "
                  OR " . $DB->sql_like('u.email', '?', false) . "
                  OR " . $DB->sql_like('c.fullname', '?', false) . "
                  OR " . $DB->sql_like('r.subject', '?', false);
    
    // One parameter for each question mark in the WHERE clause
    $params = [$searchparam, $searchparam, $searchparam, $searchparam];
}

// 3. Handle File Export (Respects Search Filter!)
if ($download) {
    $sql = "SELECT l.id, l.timesent, l.status, u.firstname, u.lastname, u.email, c.fullname AS coursename, r.subject
              FROM {local_trainingreminder_logs} l
              JOIN {user} u ON u.id = l.userid
              JOIN {course} c ON c.id = l.courseid
              JOIN {local_trainingreminder} r ON r.id = l.reminderid
              $where
          ORDER BY l.timesent DESC";
    
    $rs = $DB->get_recordset_sql($sql, $params);
    $filename = 'training_reminder_audit_' . date('Ymd_Hi');
    $columns = ['Log ID', 'Time Sent', 'First Name', 'Last Name', 'Email', 'Course', 'Rule Triggered', 'Delivery Status'];
    
    \core\dataformat::download_data(
        $filename,
        $download,
        $columns,
        $rs,
        function($log) {
            return [
                $log->id,
                userdate($log->timesent, '%Y-%m-%d %H:%M:%S'),
                $log->firstname,
                $log->lastname,
                $log->email,
                $log->coursename,
                $log->subject,
                $log->status ? 'Delivered successfully' : 'Failed'
            ];
        }
    );
    $rs->close();
    die(); 
}

// 4. Normal Page Render (UI Mode)
echo $OUTPUT->header();

echo '<div class="card"><div class="card-body">';

// Header Area
echo '<div class="d-flex justify-content-between align-items-center mb-4">';
echo '<h4>Complete Delivery History</h4>';
echo html_writer::link(new moodle_url('/local/trainingreminder/dashboard.php'), '&larr; Back to Dashboard', ['class' => 'btn btn-outline-secondary btn-sm']);
echo '</div>';

// Search Bar
echo '<form method="get" action="' . $url . '" class="mb-4">';
echo '<div class="input-group" style="max-width: 600px;">';
echo '<input type="text" name="search" class="form-control" placeholder="Search by user name, email, course, or rule..." value="' . s($search) . '">';
echo '<div class="input-group-append">';
echo '<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>';
if (!empty($search)) {
    echo html_writer::link($url, 'Clear', ['class' => 'btn btn-outline-secondary']);
}
echo '</div></div></form>';

// Export Buttons (Search string is passed into the download URLs)
echo '<div class="alert alert-info d-flex justify-content-between align-items-center">';
echo '<span><strong>Need to run a compliance audit?</strong> Export your delivery history below.</span>';
echo '<div>';
echo html_writer::link(new moodle_url($url, ['download' => 'csv', 'search' => $search]), 'Download CSV', ['class' => 'btn btn-primary btn-sm mr-2']);
echo html_writer::link(new moodle_url($url, ['download' => 'excel', 'search' => $search]), 'Download Excel', ['class' => 'btn btn-success btn-sm mr-2']);
echo '</div></div>';

// Fetch max 500 rows for the browser view to prevent page lag
$sql_logs = "SELECT l.id, l.timesent, l.status, u.firstname, u.lastname, c.fullname AS coursename, r.subject
               FROM {local_trainingreminder_logs} l
               JOIN {user} u ON u.id = l.userid
               JOIN {course} c ON c.id = l.courseid
               JOIN {local_trainingreminder} r ON r.id = l.reminderid
               $where
           ORDER BY l.timesent DESC";
$logs = $DB->get_records_sql($sql_logs, $params, 0, 500);

// Table Render
if (empty($logs)) {
    if (!empty($search)) {
        echo '<p class="text-muted text-center mt-4 border p-4 bg-light">No logs found matching "<strong>' . s($search) . '</strong>".</p>';
    } else {
        echo '<p class="text-muted">No audit logs found in the database.</p>';
    }
} else {
    echo '<div class="table-responsive">';
    $table = new html_table();
    $table->attributes['class'] = 'table table-striped table-hover table-sm border';
    $table->head = ['Date & Time', 'Recipient', 'Course', 'Triggered Rule', 'Status'];
    
    foreach ($logs as $log) {
        $statusbadge = $log->status ? '<span class="badge badge-success bg-success text-white">Delivered</span>' : '<span class="badge badge-danger bg-danger text-white">Failed</span>';
        $time = userdate($log->timesent, '%d %b %Y, %I:%M %p');
        $table->data[] = [ 
            $time, 
            format_string($log->firstname . ' ' . $log->lastname), 
            format_string($log->coursename), 
            format_string($log->subject), 
            $statusbadge 
        ];
    }
    echo html_writer::table($table);
    
    if (empty($search)) {
        echo '<p class="text-muted small mt-2">Displaying the 500 most recent deliveries. Use the export buttons above to download the entire history.</p>';
    } else {
        echo '<p class="text-muted small mt-2">Displaying search results. Use the export buttons to download this filtered list.</p>';
    }
    echo '</div>';
}

echo '</div></div>';
echo $OUTPUT->footer();
