<?php
/**
 * Main management dashboard for Training Reminder Automation.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$PAGE->set_title(get_string('pluginname', 'local_trainingreminder'));
$PAGE->set_heading(get_string('pluginname', 'local_trainingreminder'));
$PAGE->set_pagelayout('admin');

if ($action === 'savesettings' && confirm_sesskey()) {
    $master = optional_param('master_enable', 0, PARAM_INT);
    set_config('master_enable', $master, 'local_trainingreminder');
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'settings']), get_string('savesettings', 'local_trainingreminder'), null, \core\output\notification::NOTIFY_SUCCESS);
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
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), get_string('savechanges', 'local_trainingreminder'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'delete' && $campid > 0 && confirm_sesskey()) {
    $DB->delete_records('local_trainingreminder_steps', ['campid' => $campid]);
    $DB->delete_records('local_trainingreminder_logs', ['campid' => $campid]);
    $DB->delete_records('local_trainingreminder_camps', ['id' => $campid]);
    redirect(new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']), get_string('delete', 'local_trainingreminder'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

// ==========================================
// VIEW 1: ADD / EDIT CAMPAIGN FORM
// ==========================================
if ($action === 'edit' || $action === 'add') {
    $campaign = $campid > 0 ? $DB->get_record('local_trainingreminder_camps', ['id' => $campid]) : null;
    $name = $campaign ? $campaign->name : '';
    $enabled = $campaign ? $campaign->enabled : 1;
    $target_ids = $campaign ? $campaign->target_ids : 0;

    $formaction = new moodle_url('/local/trainingreminder/index.php', ['action' => 'save', 'id' => $campid]);
    $backurl = new moodle_url('/local/trainingreminder/index.php', ['tab' => 'campaigns']);

    echo html_writer::start_tag('div', ['class' => 'container-fluid']);
    echo html_writer::link($backurl, get_string('backtocampaigns', 'local_trainingreminder'), ['class' => 'btn btn-secondary mb-3']);
    echo html_writer::start_tag('form', ['method' => 'POST', 'action' => $formaction]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo '<div class="card shadow-sm mb-4"><div class="card-body">';
    echo '<h4 class="card-title">' . get_string('managecampaigns', 'local_trainingreminder') . '</h4><hr>';
    
    echo '<div class="form-group mb-3"><label><strong>' . get_string('campaignname', 'local_trainingreminder') . '</strong></label>';
    echo '<input type="text" class="form-control" name="name" value="' . s($name) . '" required></div>';
    
    $checked = $enabled ? 'checked' : '';
    echo '<div class="form-group form-check mb-3"><input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" ' . $checked . '>';
    echo '<label class="form-check-label" for="enabled">' . get_string('enablecampaign', 'local_trainingreminder') . '</label></div>';

    echo '<div class="form-group mb-3"><label><strong>' . get_string('targetaudience', 'local_trainingreminder') . '</strong></label><select class="form-control" disabled>';
    echo '<option selected>' . get_string('singlecourse', 'local_trainingreminder') . '</option>';
    echo '<option>All Courses (' . get_string('pro_badge', 'local_trainingreminder') . ')</option>';
    echo '<option>Multiple Courses (' . get_string('pro_badge', 'local_trainingreminder') . ')</option>';
    echo '<option>Selected Categories (' . get_string('pro_badge', 'local_trainingreminder') . ')</option></select></div>';

    $allcourses = $DB->get_records_select('course', 'id != 1', null, 'fullname ASC', 'id, fullname');
    echo '<div class="form-group mb-3"><label><strong>' . get_string('selectcourse', 'local_trainingreminder') . '</strong></label>';
    echo '<select class="form-control" name="single_course_id" required>';
    foreach ($allcourses as $c) {
        $sel = ($c->id == $target_ids) ? 'selected' : '';
        echo '<option value="'.$c->id.'" '.$sel.'>' . s($c->fullname) . '</option>';
    }
    echo '</select></div></div></div>';

    // LOCKED POST-RULE UI
    echo '<div class="card shadow-sm border-warning"><div class="card-body">';
    echo '<h4 class="card-title text-warning">' . get_string('postrulefallback', 'local_trainingreminder') . ' <span class="badge badge-warning text-dark ml-2">' . get_string('pro_only', 'local_trainingreminder') . '</span></h4><hr>';
    echo '<div class="alert alert-warning text-dark">' . get_string('pro_upgrade_msg', 'local_trainingreminder') . '</div>';
    echo '<div class="form-group form-check mb-3"><input type="checkbox" class="form-check-input" disabled><label class="form-check-label text-muted">' . get_string('enableloop', 'local_trainingreminder') . '</label></div>';
    echo '<button type="submit" class="btn btn-primary btn-lg mt-3">' . get_string('savechanges', 'local_trainingreminder') . '</button>';
    echo '</div></div></form></div>';
} 
// ==========================================
// VIEW 2: TABS (DASHBOARD, LOGS, SETTINGS)
// ==========================================
else {
    echo '<ul class="nav nav-tabs mb-4">';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'campaigns' ? 'active font-weight-bold' : '') . '" href="?tab=campaigns">' . get_string('tab_campaigns', 'local_trainingreminder') . '</a></li>';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'logs' ? 'active font-weight-bold' : '') . '" href="?tab=logs">' . get_string('tab_logs', 'local_trainingreminder') . '</a></li>';
    echo '<li class="nav-item"><a class="nav-link ' . ($tab === 'settings' ? 'active font-weight-bold' : '') . '" href="?tab=settings">' . get_string('tab_settings', 'local_trainingreminder') . '</a></li>';
    echo '</ul>';

    if ($tab === 'campaigns') {
        echo '<div class="row"><div class="col-md-12">';
        $total_camps = $DB->count_records('local_trainingreminder_camps');
        
        echo '<div class="d-flex justify-content-between align-items-center mb-3"><h4>' . get_string('managecampaigns', 'local_trainingreminder') . '</h4>';
        
        if ($total_camps >= 1) {
            echo '<a href="#" class="btn btn-sm btn-warning shadow-sm font-weight-bold" onclick="alert(\'' . addslashes(get_string('pro_limit_campaigns', 'local_trainingreminder')) . '\'); return false;">' . get_string('addcampaign', 'local_trainingreminder') . ' <span class="badge badge-dark">' . get_string('pro_badge', 'local_trainingreminder') . '</span></a>';
        } else {
            $addurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'add']);
            echo html_writer::link($addurl, get_string('addcampaign', 'local_trainingreminder'), ['class' => 'btn btn-sm btn-primary shadow-sm']);
        }
        echo '</div>';

        $campaigns = $DB->get_records('local_trainingreminder_camps');
        $table = new html_table();
        $table->attributes['class'] = 'table table-bordered table-hover bg-white shadow-sm';
        $table->head = [
            get_string('campaignname', 'local_trainingreminder'), 
            get_string('targetaudience', 'local_trainingreminder'), 
            get_string('status', 'local_trainingreminder'), 
            get_string('actions', 'local_trainingreminder')
        ];
        $table->data = [];

        foreach ($campaigns as $camp) {
            $editurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'edit', 'id' => $camp->id]);
            $delurl = new moodle_url('/local/trainingreminder/index.php', ['action' => 'delete', 'id' => $camp->id, 'sesskey' => sesskey()]);
            $stepsurl = new moodle_url('/local/trainingreminder/steps.php', ['campid' => $camp->id]);
            
            $target_badge = '<span class="badge badge-info">' . get_string('singlecourse', 'local_trainingreminder') . '</span>';
            $status_badge = $camp->enabled ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Disabled</span>';
            
            $actions = html_writer::link($stepsurl, get_string('managerules', 'local_trainingreminder'), ['class' => 'btn btn-sm btn-success mr-1']) . 
                       html_writer::link($editurl, get_string('edit', 'local_trainingreminder'), ['class' => 'btn btn-sm btn-outline-primary mr-1']) . 
                       html_writer::link($delurl, get_string('delete', 'local_trainingreminder'), ['class' => 'btn btn-sm btn-outline-danger', 'onclick' => 'return confirm("Are you sure?");']);

            $table->data[] = ['<strong>' . s($camp->name) . '</strong>', $target_badge, $status_badge, $actions];
        }
        if (empty($campaigns)) { echo '<div class="alert alert-info">' . get_string('nocampaigns', 'local_trainingreminder') . '</div>'; } else { echo html_writer::table($table); }
        echo '</div></div>';
    }

    if ($tab === 'logs') {
        echo '<div class="card shadow-sm mb-4"><div class="card-body"><div class="d-flex justify-content-between align-items-center">';
        echo '<div><h5 class="mb-0">' . get_string('deliverylogs', 'local_trainingreminder') . '</h5></div>';
        echo '<a href="#" class="btn btn-warning shadow-sm font-weight-bold text-dark" onclick="alert(\'' . addslashes(get_string('pro_csv_lock', 'local_trainingreminder')) . '\'); return false;"> ' . get_string('downloadcsv', 'local_trainingreminder') . ' <span class="badge badge-dark ml-1">' . get_string('pro_badge', 'local_trainingreminder') . '</span></a>';
        echo '</div></div></div>';

        $sql = "SELECT l.id, u.firstname, u.lastname, u.email, c.fullname as coursename, l.timesent, s.step_order
                FROM {local_trainingreminder_logs} l
                JOIN {user} u ON u.id = l.userid JOIN {course} c ON c.id = l.courseid 
                LEFT JOIN {local_trainingreminder_steps} s ON s.id = l.stepid ORDER BY l.timesent DESC";
        $logs = $DB->get_records_sql($sql, [], 0, 50);

        $logtable = new html_table();
        $logtable->attributes['class'] = 'table table-bordered table-striped bg-white shadow-sm';
        $logtable->head = [
            get_string('datesent', 'local_trainingreminder'), 
            get_string('recipient', 'local_trainingreminder'), 
            get_string('email', 'local_trainingreminder'), 
            get_string('course', 'local_trainingreminder'), 
            get_string('ruletriggered', 'local_trainingreminder')
        ];
        $logtable->data = [];

        foreach ($logs as $log) {
            $date = userdate($log->timesent, get_string('strftimedatetime', 'core_langconfig'));
            $logtable->data[] = [$date, s($log->firstname . ' ' . $log->lastname), s($log->email), s($log->coursename), 'Step ' . s($log->step_order)];
        }
        if (empty($logs)) { echo '<div class="alert alert-warning">' . get_string('nologs', 'local_trainingreminder') . '</div>'; } else { echo html_writer::table($logtable); }
    }
    
    if ($tab === 'settings') {
        $master_enable = get_config('local_trainingreminder', 'master_enable');
        if ($master_enable === false) $master_enable = 1; 

        echo '<div class="card shadow-sm mb-4" style="max-width: 800px;"><div class="card-body"><h4 class="card-title">' . get_string('globalsettings', 'local_trainingreminder') . '</h4><hr>';
        echo '<form method="POST" action="index.php"><input type="hidden" name="action" value="savesettings"><input type="hidden" name="sesskey" value="'.sesskey().'">';
        echo '<div class="form-group mb-4"><label><strong>' . get_string('masterengine', 'local_trainingreminder') . '</strong></label><br>';
        $chk = $master_enable ? 'checked' : '';
        echo '<div class="form-check"><input type="checkbox" class="form-check-input" id="master_enable" name="master_enable" value="1" '.$chk.'>';
        echo '<label class="form-check-label font-weight-bold" for="master_enable">' . ($master_enable ? get_string('engineactive', 'local_trainingreminder') : get_string('enginepaused', 'local_trainingreminder')) . '</label></div></div>';
        echo '<button type="submit" class="btn btn-primary">' . get_string('savesettings', 'local_trainingreminder') . '</button></form></div></div>';
    }
}
echo $OUTPUT->footer();
