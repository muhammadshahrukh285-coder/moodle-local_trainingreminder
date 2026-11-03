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
 * Privacy API implementation for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingreminder\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use context_course;

/**
 * Exports and deletes reminder delivery records associated with users.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_trainingreminder_logs', [
            'reminderid' => 'privacy:metadata:log:reminderid',
            'userid' => 'privacy:metadata:log:userid',
            'courseid' => 'privacy:metadata:log:courseid',
            'timesent' => 'privacy:metadata:log:timesent',
            'status' => 'privacy:metadata:log:status',
        ], 'privacy:metadata:log:summary');
        $collection->add_external_location_link('manageremail',
            'privacy:metadata:manageremail');
        $collection->add_subsystem_link('core_message', 'privacy:metadata:coremessage');
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $courseids = $DB->get_fieldset_select('local_trainingreminder_logs', 'DISTINCT courseid',
            'userid = :userid', ['userid' => $userid]);
        foreach ($courseids as $courseid) {
            if ($courseid && $DB->record_exists('course', ['id' => $courseid])) {
                $contextlist->add_context(context_course::instance((int)$courseid));
            }
        }
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        if ($contextlist->get_component() !== 'local_trainingreminder') {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_course) {
                continue;
            }
            $logs = $DB->get_records('local_trainingreminder_logs',
                ['userid' => $userid, 'courseid' => $context->instanceid], 'timesent ASC');
            if ($logs) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:exportpath', 'local_trainingreminder')],
                    (object)['deliverylogs' => array_values($logs)]
                );
            }
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context instanceof context_course) {
            $DB->delete_records('local_trainingreminder_logs', ['courseid' => $context->instanceid]);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        if ($contextlist->get_component() !== 'local_trainingreminder') {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_course) {
                $DB->delete_records('local_trainingreminder_logs',
                    ['userid' => $userid, 'courseid' => $context->instanceid]);
            }
        }
    }

    public static function get_users_in_context(userlist $userlist): void {
        global $DB;
        if ($userlist->get_context()->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        $courseid = $userlist->get_context()->instanceid;
        $userids = $DB->get_fieldset_select('local_trainingreminder_logs', 'DISTINCT userid',
            'courseid = :courseid', ['courseid' => $courseid]);
        $userlist->add_users($userids);
    }

    public static function delete_data_for_users(\context $context, approved_userlist $userlist): void {
        global $DB;
        if ($context instanceof context_course) {
            [$insql, $params] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            $params['courseid'] = $context->instanceid;
            $DB->delete_records_select('local_trainingreminder_logs',
                "courseid = :courseid AND userid $insql", $params);
        }
    }
}
