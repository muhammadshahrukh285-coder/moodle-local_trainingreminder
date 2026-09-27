<?php
/**
 * Global settings for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Register the new Premium Dashboard as the main plugin page.
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_trainingreminder_dashboard', 
        get_string('pluginname', 'local_trainingreminder'), 
        new moodle_url('/local/trainingreminder/dashboard.php')
    ));

    // Register the Manage Rules page.
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_trainingreminder_manage', 
        get_string('managerules', 'local_trainingreminder'), 
        new moodle_url('/local/trainingreminder/manage.php')
    ));
}
