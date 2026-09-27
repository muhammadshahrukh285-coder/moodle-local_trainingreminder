<?php
/**
 * Scheduled task class for dispatching training reminders.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingreminder\task;

defined('MOODLE_INTERNAL') || die();

class send_reminders extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('send_reminders_task', 'local_trainingreminder');
    }

    /**
     * The core logic run by the cron job.
     */
    public function execute() {
        global $DB, $CFG;

        mtrace("Starting Training Reminder Automation (Digest Mode with Completion & Recurrence Check)...");

        // Fetch all active reminder rules.
        $rules = $DB->get_records('local_trainingreminder');
        if (empty($rules)) {
            mtrace("No rules configured.");
            return;
        }

        $now = time();
        $admin = get_admin();

        // --- Fetch Manager Profile Field ID (Only query this once per run) ---
        $managerfieldname = get_config('local_trainingreminder', 'managerfield');
        $managerfieldid = null;
        if (!empty($managerfieldname)) {
            $managerfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => $managerfieldname], IGNORE_MISSING);
        }
        if ($managerfieldid) {
            mtrace("Escalation Engine Active: Monitoring profile field '{$managerfieldname}' (ID: {$managerfieldid})");
        }

        foreach ($rules as $rule) {
            mtrace("Processing Rule ID: {$rule->id}");

            $targettime = $now - ($rule->reminderdays * DAYSECS);
            $join = "";
            $where = "";
            
            // Safely check for recurring properties to prevent PHP warnings on older rules.
            $isrecurring = isset($rule->recurring) ? (int)$rule->recurring : 0;
            $frequencydays = isset($rule->frequencydays) ? (int)$rule->frequencydays : 0;
            
            // Calculate recurrence cutoff timestamp if recurring is enabled (Nag mode).
            $recurrencecutoff = $isrecurring ? $now - ($frequencydays * DAYSECS) : 0;

            // Base parameters for the SQL query.
            $params = [
                'uestatus'         => ENROL_USER_ACTIVE,
                'estatus'          => ENROL_INSTANCE_ENABLED,
                'targettime'       => $targettime,
                'reminderid'       => $rule->id,
                'recurrencecutoff' => $recurrencecutoff,
                'isrecurring'      => $isrecurring
            ];

            // 1. Build dynamic SQL for targets.
            if ($rule->targettype === 'courses') {
                if (empty($rule->targetids)) {
                    continue;
                }
                list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $rule->targetids), SQL_PARAMS_NAMED, 'cid');
                $where = " AND e.courseid $insql ";
                $params = array_merge($params, $inparams);
                
            } elseif ($rule->targettype === 'categories') {
                if (empty($rule->targetids)) {
                    continue;
                }
                list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $rule->targetids), SQL_PARAMS_NAMED, 'catid');
                $join = " JOIN {course} c ON c.id = e.courseid ";
                $where = " AND c.category $insql ";
                $params = array_merge($params, $inparams);
            }

            // 2. Query enrollments with Completion & Recurrence checks.
            $sql = "SELECT ue.id AS enrolid, u.id AS userid, u.username, u.firstname, u.lastname, 
                           u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                           u.email, u.mailformat, u.deleted, u.suspended, 
                           e.courseid, ue.timecreated AS enrollmenttime
                      FROM {user} u
                      JOIN {user_enrolments} ue ON ue.userid = u.id
                      JOIN {enrol} e ON e.id = ue.enrolid
                      $join
                     WHERE u.deleted = 0 
                       AND u.suspended = 0
                       AND ue.status = :uestatus 
                       AND e.status = :estatus
                       AND ue.timecreated <= :targettime 
                       $where
                       AND NOT EXISTS (
                           SELECT 1 
                             FROM {course_completions} cc
                            WHERE cc.course = e.courseid 
                              AND cc.userid = u.id 
                              AND cc.timecompleted > 0
                       )
                       AND NOT EXISTS (
                           SELECT 1 
                             FROM {local_trainingreminder_logs} ltrl
                            WHERE ltrl.reminderid = :reminderid 
                              AND ltrl.userid = u.id 
                              AND ltrl.courseid = e.courseid
                              AND (
                                  (:isrecurring = 0) OR 
                                  (ltrl.timesent >= :recurrencecutoff)
                              )
                       )";

            $enrollments = $DB->get_records_sql($sql, $params);
            
            if (empty($enrollments)) {
                mtrace("  -> No pending enrollments found for Rule ID {$rule->id}.");
                continue;
            }

            // 3. Group enrollments by User ID to prepare the digest.
            $users_grouped = [];
            foreach ($enrollments as $enrol) {
                if (!isset($users_grouped[$enrol->userid])) {
		   // Fetch the complete native user object to satisfy Moodle's Message API
                    $userobj = \core_user::get_user($enrol->userid, '*', MUST_EXIST);

                    $users_grouped[$enrol->userid] = (object)[
                        'user' => $userobj,
                        'courses' => []
                    ];
                }
                
                $users_grouped[$enrol->userid]->courses[] = (object)[
                    'courseid' => $enrol->courseid,
                    'enrollmenttime' => $enrol->enrollmenttime
                ];
            }

            // 4. Process one digest email per user.
            foreach ($users_grouped as $userid => $data) {
                $user = $data->user;
                
                // Build the HTML Table.
                $tablehtml = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: sans-serif;">';
                $tablehtml .= '<thead style="background-color: #f8f9fa;"><tr>';
                $tablehtml .= '<th>' . get_string('table_sno', 'local_trainingreminder') . '</th>';
                $tablehtml .= '<th>' . get_string('table_coursename', 'local_trainingreminder') . '</th>';
                $tablehtml .= '<th>' . get_string('table_enroldate', 'local_trainingreminder') . '</th>';
                $tablehtml .= '<th>' . get_string('table_status', 'local_trainingreminder') . '</th>';
                $tablehtml .= '<th>' . get_string('table_link', 'local_trainingreminder') . '</th>';
                $tablehtml .= '</tr></thead><tbody>';
                
                $sno = 1;
                foreach ($data->courses as $c) {
                    $course = $DB->get_record('course', ['id' => $c->courseid], 'id, fullname', IGNORE_MISSING);
                    if (!$course) continue;
                    
                    $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                    $enroldate = userdate($c->enrollmenttime, get_string('strftimedate'));
                    
                    $tablehtml .= '<tr>';
                    $tablehtml .= '<td style="text-align:center;">' . $sno++ . '</td>';
                    $tablehtml .= '<td>' . format_string($course->fullname) . '</td>';
                    $tablehtml .= '<td>' . $enroldate . '</td>';
                    $tablehtml .= '<td style="text-align:center;">' . get_string('status_pending', 'local_trainingreminder') . '</td>';
                    $tablehtml .= '<td style="text-align:center;"><a href="' . $courseurl->out(false) . '" style="color: #0f6cbf; text-decoration: none;">' . get_string('viewcourse', 'local_trainingreminder') . '</a></td>';
                    $tablehtml .= '</tr>';
                }
                $tablehtml .= '</tbody></table>';

                // Define the placeholder mapping array.
                $replacements = [
                    '{firstname}'    => $user->firstname,
                    '{lastname}'     => $user->lastname,
                    '{coursetable}'  => $tablehtml,
                    '{reminderdays}' => $rule->reminderdays
                ];

                // Process Subject placeholders.
                $subject = $rule->subject;
                foreach ($replacements as $search => $replace) {
                    $subject = str_replace($search, $replace, $subject);
                }

                // Process Message Body placeholders.
                $message = $rule->message;
                foreach ($replacements as $search => $replace) {
                    $message = str_replace($search, $replace, $message);
                }

                // Generate clean plain-text fallback by stripping HTML tags.
                $messagetext = html_to_text($message);

                // --- MULTI-CHANNEL NOTIFICATION ENGINE ---
                $logourl = get_config('local_trainingreminder', 'logourl');
                $brandcolor = get_config('local_trainingreminder', 'brandcolor') ?: '#0f6cbf';
                $siteurl = $CFG->wwwroot;

                $logohtml = '';
                if (!empty($logourl)) {
                    $logohtml = '<div style="text-align: center; padding: 20px 0 0 0;"><img src="'.s($logourl).'" alt="Logo" style="max-width: 220px; height: auto;"></div>';
                }

                $premium_html_message = '
                <div style="background-color: #f4f6f9; padding: 40px 20px; font-family: Arial, sans-serif;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <div style="background-color: '.s($brandcolor).'; height: 6px; width: 100%;"></div>
                        ' . $logohtml . '
                        <div style="padding: 30px 40px; color: #444444; line-height: 1.6; font-size: 15px;">
                            ' . $message . '
                        </div>
                        <div style="text-align: center; padding: 0 40px 40px;">
                            <a href="' . s($siteurl) . '/my/" style="display: inline-block; background-color: '.s($brandcolor).'; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; font-size: 16px;">' . get_string('gotodashboard', 'local_trainingreminder') . '</a>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #999999;">
                        ' . get_string('pluginname', 'local_trainingreminder') . ' &copy; ' . date('Y') . '
                    </div>
                </div>';

                // Send Native Moodle Notification (Triggers Bell + Email + Mobile Push)
                $eventdata = new \core\message\message();
                $eventdata->courseid          = SITEID;
                $eventdata->component         = 'local_trainingreminder';
                $eventdata->name              = 'reminder';
		$eventdata->userfrom          = \core_user::get_noreply_user();
                $eventdata->userto            = $user;
                $eventdata->subject           = $subject;
                $eventdata->fullmessage       = $messagetext;
                $eventdata->fullmessageformat = FORMAT_HTML;
                $eventdata->fullmessagehtml   = $premium_html_message;
                $eventdata->smallmessage      = $subject; // This powers the dropdown Bell text
                $eventdata->notification      = 1;
                
                $emailresult = message_send($eventdata);

                // --- MANAGER ESCALATION CC BLOCK (Now with Bell support!) ---
                if ($emailresult && $managerfieldid) {
                    $manageremail = $DB->get_field('user_info_data', 'data', ['userid' => $user->id, 'fieldid' => $managerfieldid], IGNORE_MISSING);
                    
                    if (!empty($manageremail) && filter_var($manageremail, FILTER_VALIDATE_EMAIL)) {
                        
                        $manager = $DB->get_record('user', ['email' => $manageremail, 'deleted' => 0], '*', IGNORE_MISSING);
                        $is_real_user = true;
                        
                        if (!$manager) {
                            $is_real_user = false;
                            $manager = new \stdClass();
                            $manager->id = -99;
                            $manager->email = $manageremail;
                            $manager->firstname = 'Line Manager';
                            $manager->lastname = '';
                            $manager->firstnamephonetic = '';
                            $manager->lastnamephonetic = '';
                            $manager->middlename = '';
                            $manager->alternatename = '';
                            $manager->mailformat = 1;
                        }
                        
                        $ccsubject = "Escalation CC: " . $subject;
                        $ccwarning = "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ffeeba; font-family: sans-serif;'><strong>Manager Escalation CC:</strong> Your direct report <strong>{$user->firstname} {$user->lastname}</strong> is overdue for mandatory training and has received the automated notification below.</div>";
                        $cchtml = str_replace('<div style="padding: 30px 40px; color: #444444; line-height: 1.6; font-size: 15px;">', '<div style="padding: 30px 40px; color: #444444; line-height: 1.6; font-size: 15px;">' . $ccwarning, $premium_html_message);
                        $cctext = "MANAGER ESCALATION CC: Your direct report {$user->firstname} {$user->lastname} received the following reminder:\n\n" . $messagetext;
                        
                        if ($is_real_user) {
                            // If they have an account, trigger the Manager's Bell!
                            $cc_eventdata = clone $eventdata;
                            $cc_eventdata->userto = $manager;
                            $cc_eventdata->subject = $ccsubject;
                            $cc_eventdata->fullmessage = $cctext;
                            $cc_eventdata->fullmessagehtml = $cchtml;
                            $cc_eventdata->smallmessage = $ccsubject;
                            message_send($cc_eventdata);
                            mtrace("    -> Triggered Bell & Email for Line Manager: " . $manageremail);
                        } else {
                            // External email address fallback
                            email_to_user($manager, $admin, $ccsubject, $cctext, $cchtml);
                            mtrace("    -> Emailed External Line Manager: " . $manageremail);
                        }
                    }
                }
                // -----------------------------------

                // Insert a log for EVERY course they were reminded about.
                foreach ($data->courses as $c) {
                    $log = new \stdClass();
                    $log->reminderid = $rule->id;
                    $log->courseid   = $c->courseid;
                    $log->userid     = $user->id;
                    $log->timesent   = $now;
                    $log->status     = $emailresult ? 1 : 0;
                    $DB->insert_record('local_trainingreminder_logs', $log);
                }

                mtrace("  -> Sent digest reminder to User ID: {$user->id} (Courses included: " . count($data->courses) . ")");
            }
        }

        mtrace("Training Reminder Automation completed.");
    }
}
