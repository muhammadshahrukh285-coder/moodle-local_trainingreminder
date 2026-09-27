<?php
/**
 * Database upgrade script for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_trainingreminder_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026092802) {
        $table = new xmldb_table('local_trainingreminder');

        // Add the recurring field.
        $field1 = new xmldb_field('recurring', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'reminderdays');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Add the frequencydays field.
        $field2 = new xmldb_field('frequencydays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'recurring');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_plugin_savepoint(true, 2026092802, 'local', 'trainingreminder');
    }

    return true;
}
