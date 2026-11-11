# Privacy information — Training Reminder Automation

**Plugin component:** `local_trainingreminder`  
**Maintainer:** Muhammad Shahrukh  
**License:** GNU GPL v3 or later

## Overview

Training Reminder Automation is a Moodle local plugin that helps site administrators send automated course-completion reminders and, where configured, escalate reminders to a learner's manager.

The plugin operates within the Moodle site where it is installed. The Moodle site owner or administrator is responsible for determining the purposes and lawful basis for processing learner information, configuring recipients, setting retention practices, and responding to privacy requests.

## Information the plugin uses or stores

The plugin uses Moodle user, course, and enrolment information to identify learners who match administrator-configured reminder rules. This can include Moodle user identifiers, course identifiers, enrolment timing, and course-completion status. These Moodle records remain part of Moodle's existing data; the plugin does not create a separate learner profile database.

The plugin stores the following information in its own database tables:

- **Reminder delivery records:** reminder rule ID, Moodle course ID, Moodle user ID, send timestamp, and delivery status.
- **Reminder rule configuration:** rule target and conditions, reminder interval settings, notification subject and message template, creation/modification timestamps, and the Moodle user ID of the person recorded as having modified the rule.
- **Manager contact detail:** when manager escalation is configured, the plugin reads the manager email address from the Moodle user profile field selected by the site administrator. The address may be used as a notification destination; it is not listed as a field in the plugin's delivery-log table.

Administrators should avoid placing sensitive or unnecessary personal information in reminder subjects, message templates, or rule configuration.

## How information is used and shared

The information is used to:

1. identify learners who meet the conditions of a reminder rule;
2. send course-completion reminders through Moodle's messaging infrastructure;
3. send manager escalation notifications when configured by the site administrator; and
4. maintain an audit history of reminder dispatch activity.

Notifications to external manager email addresses are sent using the Moodle site's configured email delivery infrastructure. Moodle's core messaging and mail systems may process or retain message information according to the site's configuration and policies. The plugin does not independently operate an external analytics or advertising service.

## Access and security

Access to plugin administration pages and audit information is controlled through Moodle permissions and the plugin's capabilities. The Moodle site administrator is responsible for securing the Moodle installation, restricting administrative access, maintaining backups, and configuring the site's mail services appropriately.

## Retention

The plugin does not define a separate automatic retention period for its delivery logs. The Moodle site administrator should establish and document an appropriate retention schedule for the site's operational and compliance requirements, and remove data when it is no longer required, subject to applicable obligations.

## Privacy requests and deletion

The plugin implements Moodle's Privacy API. Through Moodle's data privacy tools, personal data handled by the plugin can be included in applicable export and deletion requests.

The plugin's privacy provider exports a user's reminder delivery records and the user's rule-modification references. On deletion, the provider deletes the user's delivery records in the relevant course contexts and anonymises the user reference on shared reminder rules rather than deleting those shared rules. A course-context deletion removes the plugin's delivery records for that course.

Moodle site administrators should use Moodle's privacy tools and follow their site's procedures for handling access, export, correction, and deletion requests. Data held by Moodle core messaging, email infrastructure, backups, or other integrations may require separate handling under the site's policies.

## Contact

For questions about this plugin's privacy implementation, contact the plugin maintainer through the issue tracker:

https://github.com/muhammadshahrukh285-coder/moodle-local_trainingreminder/issues

For questions about a particular Moodle site's use of the plugin or requests concerning learner data, contact that Moodle site's administrator or the organisation responsible for the site.
