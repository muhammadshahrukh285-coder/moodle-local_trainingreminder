<?php
/**
 * English language strings for Training Reminder.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Training Reminder Automation';
$string['send_reminders_task'] = 'Process and dispatch automated training reminders';
$string['managerules'] = 'Manage reminder rules';
$string['addrule'] = 'Add new reminder rule';
$string['editrule'] = 'Edit reminder rule';
$string['deleterule'] = 'Delete rule';
$string['ruledeleted'] = 'Reminder rule successfully deleted.';
$string['norules'] = 'No reminder rules have been configured yet.';
$string['reminderdays'] = 'Days since enrollment';
$string['reminderdays_help'] = 'Number of days after enrollment before the user receives this email.';
$string['subject'] = 'Email subject';
$string['message'] = 'Email body content';
$string['message_help'] = 'You can use placeholders like {firstname} and {coursetable}.';
$string['actions'] = 'Actions';
$string['targettype'] = 'Target Type';
$string['targettype_all'] = 'All Courses';
$string['targettype_categories'] = 'Specific Categories';
$string['targettype_courses'] = 'Specific Courses';
$string['selectcategories'] = 'Select Categories';
$string['selectcourses'] = 'Select Courses';
$string['target'] = 'Target';
$string['generalsettings'] = 'General settings';
$string['emailsettings'] = 'Email template settings';

// Table Headers
$string['table_sno'] = 'S.No';
$string['table_coursename'] = 'Course Name';
$string['table_enroldate'] = 'Enrollment Date';
$string['table_status'] = 'Status';
$string['table_link'] = 'Course Link';
$string['viewcourse'] = 'View Course';
$string['status_pending'] = 'Pending';

// Default templates
$string['default_subject'] = 'Reminder: Action Required for Your Enrolled Courses';
$string['default_message'] = '<p>Hello {firstname},</p>
<p>This is a friendly reminder regarding your active training enrollments. It has been {reminderdays} days since you were enrolled in the following courses:</p>
{coursetable}
<p><br>Please log in to the learning portal to review your progress and complete any pending activities.</p>
<p>Best regards,<br>The Learning Team</p>';

$string['placeholders_help'] = 'Available placeholders:<br>
<strong>{firstname}</strong> - The user\'s first name<br>
<strong>{lastname}</strong> - The user\'s last name<br>
<strong>{coursetable}</strong> - Generates a table of all pending courses for this user<br>
<strong>{reminderdays}</strong> - The days threshold configured for this rule';

$string['recurring'] = 'Enable recurring reminders (Nag mode)';
$string['recurring_help'] = 'If enabled, reminders will repeat periodically after the initial threshold is met until the course is completed.';
$string['frequencydays'] = 'Repeat frequency (Days)';
$string['frequencydays_help'] = 'How many days to wait before sending another reminder email to the user.';
$string['settingshdr'] = 'Automation Schedule Settings';

// Premium UI Settings
$string['logourl'] = 'Company Logo URL';
$string['logourl_desc'] = 'Enter the full URL to your company logo (e.g., https://yoursite.com/logo.png). Leave blank for no logo.';
$string['brandcolor'] = 'Brand Primary Color';
$string['brandcolor_desc'] = 'Enter the HEX color code for the email header and buttons (e.g., #0f6cbf).';
$string['gotodashboard'] = 'Go to Learning Dashboard';

// Notification Provider
$string['messageprovider:reminder'] = 'Automated Training Reminders';

// Capabilities
$string['trainingreminder:manage'] = 'Manage training reminder automation rules';

// Privacy API Strings (GDPR Compliance)
$string['privacy:metadata:log:summary'] = 'Stores a record of automated training reminders sent to users to prevent spamming.';
$string['privacy:metadata:log:userid'] = 'The ID of the user who received the reminder.';
$string['privacy:metadata:log:courseid'] = 'The ID of the course they were reminded about.';
$string['privacy:metadata:log:timesent'] = 'The timestamp when the reminder was dispatched.';
