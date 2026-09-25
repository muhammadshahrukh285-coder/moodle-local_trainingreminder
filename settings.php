<?php
/**
 * System settings for Training Reminder Automation.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_trainingreminder_category', get_string('pluginname', 'local_trainingreminder')));

    $settings = new admin_settingpage('local_trainingreminder_settings', get_string('globalsettings', 'local_trainingreminder'));
    $ADMIN->add('local_trainingreminder_category', $settings);

    $dashboardurl = new moodle_url('/local/trainingreminder/index.php');
    $buttonhtml = html_writer::link($dashboardurl, get_string('launchdashboard', 'local_trainingreminder'), ['class' => 'btn btn-primary btn-lg mt-2 mb-4 shadow-sm']);
    
    $settings->add(new admin_setting_heading('local_trainingreminder/dashboard_heading', 
        get_string('dashboardheading', 'local_trainingreminder'), 
        get_string('dashboarddesc', 'local_trainingreminder') . '<br><br>' . $buttonhtml));
        
    $settings->add(new admin_setting_heading('local_trainingreminder/pro_heading', 
        '<span style="color: #856404; background-color: #fff3cd; padding: 5px 10px; border-radius: 4px; border: 1px solid #ffeeba;">' . get_string('pro_only', 'local_trainingreminder') . '</span>', 
        get_string('prouplock', 'local_trainingreminder')));
}
