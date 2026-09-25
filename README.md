# Training Reminder Automation for Moodle

A powerful automated email reminder engine for Moodle. Drive course completion rates by automatically following up with enrolled users who haven't finished their training.

## Features (Lite Edition)
* **Automated Escalation:** Create up to 2 timed reminder rules (e.g., 7 days and 14 days after enrollment).
* **Targeting:** Target any single course on your Moodle site.
* **Delivery Audit Log:** View a complete history of all reminders sent directly in your browser.
* **HTML Templates:** Beautiful, mobile-responsive email templates utilizing Moodle tags (`{{firstname}}`, `{{coursename}}`, etc.).
* **Kill Switch:** Instantly pause all outgoing reminders across the site.

## 🚀 Upgrade to PRO for Enterprise
The **Premium Edition** is available on our website and unlocks enterprise compliance features:
* **Unlimited Campaigns & Rules:** Build infinite escalation ladders across every course on your site.
* **Infinite Loop (Post-Rule):** Keep reminding users every X days indefinitely until they complete the course.
* **CSV Audit Exports:** 1-click download of delivery logs for compliance auditors.
* **Email Batch Throttling:** Protect your SMTP server from spam blacklists.
* **Custom Sender Overrides:** Send emails from `hr@yourcompany.com` instead of the default Moodle no-reply address.

## Installation
1. Download the latest `.zip` release.
2. Unzip the contents into the `/local/` directory of your Moodle installation.
3. Ensure the folder is named `trainingreminder`.
4. Log in as an Administrator and follow the standard Moodle upgrade prompts, or run `php admin/cli/upgrade.php`.

## License
2026 Muhammad Shahrukh. Licensed under the GNU GPL v3 or later.
