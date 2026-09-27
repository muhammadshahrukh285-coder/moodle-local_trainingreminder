<?php
/**
 * Privacy Subsystem implementation for local_trainingreminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingreminder\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;

/**
 * Privacy Subsystem for local_trainingreminder implementing metadata provider.
 */
class provider implements \core_privacy\local\metadata\provider {

    /**
     * Returns metadata about the user information stored by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_trainingreminder_logs', [
            'userid' => 'privacy:metadata:log:userid',
            'courseid' => 'privacy:metadata:log:courseid',
            'timesent' => 'privacy:metadata:log:timesent'
        ], 'privacy:metadata:log:summary');

        return $collection;
    }
}
