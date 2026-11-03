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
 * Site administration settings for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_trainingreminder_settings',
        get_string('pluginsettings', 'local_trainingreminder'));
    $settings->add(new admin_setting_configtext('local_trainingreminder/logourl',
        get_string('logourl', 'local_trainingreminder'),
        get_string('logourl_desc', 'local_trainingreminder'), '', PARAM_URL));
    $settings->add(new admin_setting_configcolourpicker('local_trainingreminder/brandcolor',
        get_string('brandcolor', 'local_trainingreminder'),
        get_string('brandcolor_desc', 'local_trainingreminder'), '#0f6cbf'));
    $settings->add(new admin_setting_configtext('local_trainingreminder/managerfield',
        get_string('managerfield', 'local_trainingreminder'),
        get_string('managerfield_desc', 'local_trainingreminder'), 'line_manager_email', PARAM_ALPHANUMEXT));
    $ADMIN->add('localplugins', $settings);

    $capability = 'local/trainingreminder:manage';
    $ADMIN->add('localplugins', new admin_externalpage('local_trainingreminder_dashboard',
        get_string('dashboard', 'local_trainingreminder'),
        new moodle_url('/local/trainingreminder/dashboard.php'), $capability));
    $ADMIN->add('localplugins', new admin_externalpage('local_trainingreminder_manage',
        get_string('managerules', 'local_trainingreminder'),
        new moodle_url('/local/trainingreminder/manage.php'), $capability));
    $ADMIN->add('localplugins', new admin_externalpage('local_trainingreminder_logs',
        get_string('auditlogs', 'local_trainingreminder'),
        new moodle_url('/local/trainingreminder/logs.php'), $capability));
}
