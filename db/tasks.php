<?php
/**
 * Scheduled tasks definitions for the local_trainingreminder plugin.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_trainingreminder\task\send_reminders',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '8',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];
