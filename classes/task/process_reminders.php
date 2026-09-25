<?php
/**
 * Scheduled task to process and send automated training reminders.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingreminder\task;
defined('MOODLE_INTERNAL') || die();

class process_reminders extends \core\task\scheduled_task {
    
    public function get_name() {
        return get_string('taskprocessreminders', 'local_trainingreminder');
    }

    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/message/lib.php');
        
        mtrace("============================================");
        mtrace("Starting Training Reminder Engine (LITE VERSION)...");

        $master = get_config('local_trainingreminder', 'master_enable');
        if ($master !== false && $master == 0) {
            mtrace("GLOBAL KILL SWITCH IS ENGAGED. Engine paused.");
            return;
        }

        $campaigns = $DB->get_records('local_trainingreminder_camps', ['enabled' => 1]);
        if (empty($campaigns)) return;

        $now = time();
        $sender = \core_user::get_noreply_user();

        foreach ($campaigns as $camp) {
            $steps = $DB->get_records('local_trainingreminder_steps', ['campid' => $camp->id], 'step_order ASC');
            if (empty($steps) || empty($camp->target_ids)) continue;

            $target_id = (int)$camp->target_ids;

            $sql = "SELECT ue.id as ueid, u.id as userid, c.id as courseid, 
                           ue.timestart, ue.timecreated, c.fullname as coursename,
                           u.firstname, u.lastname, u.email
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON e.id = ue.enrolid
                      JOIN {user} u ON u.id = ue.userid
                      JOIN {course} c ON c.id = e.courseid
                     WHERE u.deleted = 0 AND u.suspended = 0 
                       AND ue.status = 0 AND e.status = 0
                       AND c.id = :courseid";
            
            $rs = $DB->get_recordset_sql($sql, ['courseid' => $target_id]);
            
            foreach ($rs as $enrollment) {
                $completion = $DB->get_record('course_completions', ['userid' => $enrollment->userid, 'course' => $enrollment->courseid]);
                if ($completion && !empty($completion->timecompleted)) continue; 

                $start_time = ($enrollment->timestart > 0) ? $enrollment->timestart : $enrollment->timecreated;
                $days_enrolled = floor(($now - $start_time) / 86400);

                foreach ($steps as $step) {
                    $log_exists = $DB->record_exists('local_trainingreminder_logs', [
                        'userid' => $enrollment->userid, 'courseid' => $enrollment->courseid, 'campid' => $camp->id, 'stepid' => $step->id
                    ]);

                    if (!$log_exists) {
                        if ($days_enrolled >= $step->delay_days) {
                            $subject = str_replace('{{coursename}}', $enrollment->coursename, $step->subject);
                            $body = str_replace(
                                ['{{firstname}}', '{{lastname}}', '{{coursename}}', '{{siteurl}}'], 
                                [$enrollment->firstname, $enrollment->lastname, $enrollment->coursename, $CFG->wwwroot], 
                                $step->body_template
                            );

                            $message = new \core\message\message();
                            $message->component = 'local_trainingreminder';
                            $message->name = 'training_reminder';
                            $message->userfrom = $sender;
                            $message->userto = $enrollment->userid;
                            $message->subject = $subject;
                            $message->fullmessage = strip_tags($body);
                            $message->fullmessageformat = FORMAT_HTML;
                            $message->fullmessagehtml = $body;
                            $message->notification = 1;
                            
                            message_send($message);
                            
                            $log = new \stdClass();
                            $log->userid = $enrollment->userid; $log->courseid = $enrollment->courseid; $log->campid = $camp->id; $log->stepid = $step->id; $log->is_post_rule = 0; $log->timesent = time();
                            $DB->insert_record('local_trainingreminder_logs', $log);
                            break; 
                        }
                    }
                }
            }
            $rs->close();
        }
        mtrace("Finished Training Reminder Engine.");
    }
}
