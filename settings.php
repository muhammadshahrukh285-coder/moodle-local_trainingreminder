<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_trainingreminder_category', 'Training Reminder'));

    $settings = new admin_settingpage('local_trainingreminder_settings', 'General Settings');
    $ADMIN->add('local_trainingreminder_category', $settings);

    $dashboardurl = new moodle_url('/local/trainingreminder/index.php');
    $buttonhtml = html_writer::link($dashboardurl, 'Launch Management Dashboard', ['class' => 'btn btn-primary btn-lg mt-2 mb-4 shadow-sm']);
    
    $settings->add(new admin_setting_heading('local_trainingreminder/dashboard_heading', 
        'Plugin Dashboard', 
        'Click the button below to manage your reminder campaigns, customize email rules, and view the delivery audit logs.<br><br>' . $buttonhtml));
        
    $settings->add(new admin_setting_heading('local_trainingreminder/pro_heading', 
        '<span style="color: #856404; background-color: #fff3cd; padding: 5px 10px; border-radius: 4px; border: 1px solid #ffeeba;">UPGRADE TO PRO</span>', 
        'Unlock advanced system settings including <strong>Email Batch Throttling</strong> and <strong>Custom Sender Email (e.g. hr@company.com)</strong> by upgrading to the Premium edition.'));
}
