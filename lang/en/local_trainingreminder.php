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

// Administration interface.
$string['pluginsettings'] = 'Training reminder settings';
$string['dashboard'] = 'Dashboard';
$string['auditlogs'] = 'Audit logs';
$string['managerfield'] = 'Manager email profile field';
$string['managerfield_desc'] = 'Shortname of the user profile field containing the manager email address. This address may receive a copy of reminder messages.';
$string['rulesaved'] = 'Reminder rule saved successfully.';
$string['copylabel'] = 'Copy';
$string['ruleduplicated'] = 'Reminder rule duplicated successfully.';
$string['activereminders'] = 'Active reminders';
$string['createnewrule'] = 'Create new rule';
$string['triggerday'] = 'Trigger day';
$string['frequency'] = 'Frequency';
$string['once'] = 'Once';
$string['everyxdays'] = 'Every {$a} days';
$string['clone'] = 'Clone';
$string['confirmdelete'] = 'Delete this rule and all associated delivery logs?';
$string['deliveryhistory'] = 'Complete delivery history';
$string['logsearchplaceholder'] = 'Search by user name, email, course, or rule';
$string['auditprompt'] = 'Export delivery history for a compliance audit.';
$string['downloadcsv'] = 'Download CSV';
$string['downloadexcel'] = 'Download Excel';
$string['recipient'] = 'Recipient';
$string['nologsfound'] = 'No audit logs found.';
$string['nologsmatch'] = 'No logs match your search.';
$string['loglimit'] = 'Showing the 500 most recent deliveries. Export to download the complete history.';
$string['logfiltered'] = 'Showing filtered search results. Export to download the complete matching list.';
$string['logid'] = 'Log ID';
$string['timesent'] = 'Time sent';
$string['ruletriggered'] = 'Triggered rule';
$string['deliverystatus'] = 'Delivery status';
$string['delivered'] = 'Delivered';
$string['failed'] = 'Failed';
$string['totalsent'] = 'Total sent';
$string['last7days'] = 'Last 7 days';
$string['deliveryrate'] = 'Delivery rate';
$string['activerules'] = 'Active rules';
$string['emailvolume'] = 'Email volume (last 7 days)';
$string['toprules'] = 'Top rules by volume';
$string['notriggered'] = 'No rules triggered yet.';
$string['configure'] = 'Configure settings';
$string['sendtestemail'] = 'Send test email';
$string['pluginadministration'] = 'Plugin administration';
$string['configurerules'] = 'Configure automation rules';
$string['edittasktiming'] = 'Edit scheduled task timing';
$string['recentdeliveries'] = 'Recent deliveries (last 5)';
$string['viewauditlog'] = 'View full audit log and export';
$string['testemailbody'] = 'Hello {$a}, this is a test preview of your Training Reminder email template.';
$string['testemailsubject'] = 'Test preview: Training Reminder';
$string['testemailsent'] = 'Test email sent to {$a}.';
$string['companylogo'] = 'Company logo';
$string['taskstarted'] = 'Starting Training Reminder Automation.';
$string['norulesconfigured'] = 'No reminder rules are configured.';
$string['processingrule'] = 'Processing reminder rule {$a}.';
$string['nopendingenrolments'] = 'No pending enrolments found for reminder rule {$a}.';
$string['taskcompleted'] = 'Training Reminder Automation completed.';
$string['privacy:metadata:log:reminderid'] = 'The identifier of the reminder rule that generated the delivery.';
$string['privacy:metadata:log:status'] = 'Whether the reminder message was successfully submitted for delivery.';
$string['privacy:metadata:manageremail'] = 'A manager email address stored in a user profile field may receive a copy of the reminder, including information about the user and their overdue training.';
$string['privacy:metadata:coremessage'] = 'The plugin uses Moodle messaging and email delivery services to send reminder notifications to recipients.';
$string['privacy:exportpath'] = 'Training reminder delivery history';
$string['privacy:metadata:rule:summary'] = 'Stores the user ID of the user who last modified each reminder rule.';
$string['privacy:metadata:rule:usermodified'] = 'The user ID of the user who last modified the reminder rule.';
$string['privacy:ruleexportpath'] = 'Reminder rules modified';

$string['id'] = 'ID';
$string['invalidtargettype'] = 'Skipping rule {$a}: invalid target type.';
$string['reminderssent'] = 'Reminders sent';
