<?php
/**
 * Training Reminder language strings.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Training Reminder Automation';
$string['taskprocessreminders'] = 'Process and send training reminders';

// Tabs
$string['tab_campaigns'] = 'Dashboard & Campaigns';
$string['tab_logs'] = 'Delivery Logs';
$string['tab_settings'] = 'Global Settings';

// Campaign Management
$string['managecampaigns'] = 'Manage Campaigns';
$string['addcampaign'] = '+ Create New Campaign';
$string['campaignname'] = 'Campaign Name';
$string['enablecampaign'] = 'Enable this campaign';
$string['targetaudience'] = 'Target Audience';
$string['singlecourse'] = 'Single Course (Free Version Limit)';
$string['selectcourse'] = 'Select Target Course:';
$string['status'] = 'Status';
$string['actions'] = 'Actions';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['managerules'] = 'Manage Rules';
$string['test'] = 'Test';
$string['nocampaigns'] = 'No reminder campaigns found.';

// Rules & Steps
$string['managesteps'] = 'Manage Reminder Rules';
$string['addrule'] = 'Add New Reminder Rule';
$string['steporder'] = 'Step Order (e.g., 1 for first reminder, 2 for second)';
$string['delaydays'] = 'Delay (Days after Enrolment)';
$string['emailsubject'] = 'Email Subject';
$string['bodytemplate'] = 'Email Body Template (HTML)';
$string['savechanges'] = 'Save Changes';
$string['backtocampaigns'] = '&larr; Back to Campaigns';
$string['backtorules'] = '&larr; Back to Rules';

// Logs
$string['deliverylogs'] = 'Delivery Audit Trail';
$string['downloadcsv'] = 'Download CSV';
$string['datesent'] = 'Date Sent';
$string['recipient'] = 'Recipient';
$string['email'] = 'Email';
$string['course'] = 'Course';
$string['ruletriggered'] = 'Rule Triggered';
$string['nologs'] = 'No deliveries yet.';

// Settings
$string['globalsettings'] = 'Sitewide Preferences';
$string['masterengine'] = 'Master Engine Switch';
$string['engineactive'] = 'ENGINE IS ACTIVE';
$string['enginepaused'] = 'ENGINE IS PAUSED';
$string['savesettings'] = 'Save Global Settings';

// PRO Badges & Paywalls
$string['pro_badge'] = 'PRO';
$string['pro_only'] = 'PRO VERSION ONLY';
$string['pro_upgrade_msg'] = 'Upgrade to Training Reminder PRO to unlock infinite recurring reminders!';
$string['pro_limit_campaigns'] = 'FREE VERSION LIMIT REACHED: You can only have 1 active campaign. Upgrade to PRO for unlimited campaigns targeting every course!';
$string['pro_limit_rules'] = 'FREE VERSION LIMIT REACHED: You are limited to 2 rules per campaign. Upgrade to PRO to build unlimited escalation ladders!';
$string['pro_csv_lock'] = 'UPGRADE TO PRO: One-click CSV Audit Exports are only available in the Premium edition.';
$string['postrulefallback'] = 'Post-Rule Fallback';
$string['enableloop'] = 'Enable Recurring Reminders (Infinite Loop)';

$string['dashboardheading'] = 'Plugin Dashboard';
$string['dashboarddesc'] = 'Click the button below to manage your reminder campaigns, customize email rules, and view the delivery audit logs.';
$string['launchdashboard'] = 'Launch Management Dashboard';
$string['prouplock'] = 'Unlock advanced system settings including <strong>Email Batch Throttling</strong> and <strong>Custom Sender Email</strong> by upgrading to the Premium edition.';
