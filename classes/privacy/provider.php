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
use core_privacy\local\request\transform;
use context;
use context_course;
use context_system;

/**
 * Exports and deletes reminder delivery records and rule-modification references.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe the personal data stored by this plugin.
     *
     * @param collection $collection The metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_trainingreminder_logs', [
            'reminderid' => 'privacy:metadata:log:reminderid',
            'userid' => 'privacy:metadata:log:userid',
            'courseid' => 'privacy:metadata:log:courseid',
            'timesent' => 'privacy:metadata:log:timesent',
            'status' => 'privacy:metadata:log:status',
        ], 'privacy:metadata:log:summary');

        $collection->add_database_table('local_trainingreminder', [
            'usermodified' => 'privacy:metadata:rule:usermodified',
        ], 'privacy:metadata:rule:summary');

        $collection->add_external_location_link('manageremail',
            'privacy:metadata:manageremail');
        $collection->add_subsystem_link('core_message', 'privacy:metadata:coremessage');
        return $collection;
    }

    /**
     * Find course contexts containing delivery logs and the system context containing rule editor references.
     *
     * @param int $userid The user ID.
     * @return contextlist
     */
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

        if ($DB->record_exists('local_trainingreminder', ['usermodified' => $userid])) {
            $contextlist->add_context(context_system::instance());
        }

        return $contextlist;
    }

    /**
     * Export the user's delivery logs and reminder-rule editor references.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if ($contextlist->get_component() !== 'local_trainingreminder' || empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_course) {
                $logs = $DB->get_records('local_trainingreminder_logs',
                    ['userid' => $userid, 'courseid' => $context->instanceid], 'timesent ASC');
                if ($logs) {
                    writer::with_context($context)->export_data(
                        [get_string('privacy:exportpath', 'local_trainingreminder')],
                        (object)['deliverylogs' => array_values($logs)]
                    );
                }
            } else if ($context instanceof context_system) {
                $rules = $DB->get_records('local_trainingreminder',
                    ['usermodified' => $userid], 'id ASC', 'id, timecreated, timemodified');
                if ($rules) {
                    $export = [];
                    foreach ($rules as $rule) {
                        $export[] = (object)[
                            'ruleid' => (int)$rule->id,
                            'timecreated' => transform::datetime($rule->timecreated),
                            'timemodified' => transform::datetime($rule->timemodified),
                        ];
                    }
                    writer::with_context($context)->export_data(
                        [get_string('privacy:ruleexportpath', 'local_trainingreminder')],
                        (object)['modifiedrules' => $export]
                    );
                }
            }
        }
    }

    /**
     * Delete all plugin personal data in a context.
     *
     * @param context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context instanceof context_course) {
            $DB->delete_records('local_trainingreminder_logs', ['courseid' => $context->instanceid]);
        } else if ($context instanceof context_system) {
            // Preserve shared rules but remove references to the users who last modified them.
            $DB->set_field('local_trainingreminder', 'usermodified', 0);
        }
    }

    /**
     * Delete a user's plugin data in approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if ($contextlist->get_component() !== 'local_trainingreminder' || empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_course) {
                $DB->delete_records('local_trainingreminder_logs',
                    ['userid' => $userid, 'courseid' => $context->instanceid]);
            } else if ($context instanceof context_system) {
                // Keep operational rules but anonymise the last-modified user reference.
                $DB->set_field('local_trainingreminder', 'usermodified', 0, ['usermodified' => $userid]);
            }
        }
    }

    /**
     * Find users with plugin data in the supplied context.
     *
     * @param userlist $userlist The user list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context instanceof context_course) {
            $userids = $DB->get_fieldset_select('local_trainingreminder_logs', 'DISTINCT userid',
                'courseid = :courseid', ['courseid' => $context->instanceid]);
            $userlist->add_users($userids);
        } else if ($context instanceof context_system) {
            $userlist->add_from_sql('usermodified',
                "SELECT usermodified FROM {local_trainingreminder} WHERE usermodified > 0",
                []);
        }
    }

    /**
     * Delete multiple users' data in one context.
     *
     * @param approved_userlist $userlist The approved user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        if ($context instanceof context_course) {
            $params['courseid'] = $context->instanceid;
            $DB->delete_records_select('local_trainingreminder_logs',
                "courseid = :courseid AND userid {$insql}", $params);
        } else if ($context instanceof context_system) {
            $DB->execute("UPDATE {local_trainingreminder}
                             SET usermodified = 0
                           WHERE usermodified {$insql}", $params);
        }
    }
}
