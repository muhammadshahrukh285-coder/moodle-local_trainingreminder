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
 * Database upgrade script for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_trainingreminder_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026092802) {
        $table = new xmldb_table('local_trainingreminder');
        $field1 = new xmldb_field('recurring', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'reminderdays');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('frequencydays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'recurring');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2026092802, 'local', 'trainingreminder');
    }

    if ($oldversion < 2026110300) {
        $ruletable = new xmldb_table('local_trainingreminder');
        $messageformat = new xmldb_field('messageformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'message');
        if (!$dbman->field_exists($ruletable, $messageformat)) {
            $dbman->add_field($ruletable, $messageformat);
        }

        $logtable = new xmldb_table('local_trainingreminder_logs');
        $logtable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $logtable->add_field('reminderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $logtable->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reminderid');
        $logtable->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'courseid');
        $logtable->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        $logtable->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timesent');
        $logtable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $logtable->add_index('reminderid_ix', XMLDB_INDEX_NOTUNIQUE, ['reminderid']);
        $logtable->add_index('courseid_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        $logtable->add_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->table_exists($logtable)) {
            $dbman->create_table($logtable);
        }
        upgrade_plugin_savepoint(true, 2026110300, 'local', 'trainingreminder');
    }

    return true;
}
