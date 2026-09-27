# Training Reminder Automation for Moodle

**Plugin Type:** Local (`local_trainingreminder`)  
**Author:** Muhammad Shahrukh  
**License:** GNU GPL v3 or later  

An enterprise-grade training automation engine for Moodle. This plugin allows administrators to configure dynamic, recurring reminder rules that automatically notify users who have not completed their mandatory enrolled courses. 

Designed for corporate and institutional environments, it features multi-channel delivery, an intelligent Manager Escalation engine, and a premium dashboard command center.

## ✨ Key Features

* **Multi-Channel Delivery Engine:** Seamlessly integrates with Moodle's native Message API to deliver alerts via the Moodle Bell UI, Mobile Push Notifications, and beautifully branded HTML emails.
* **Intelligent Manager Escalation:** Automatically carbon-copies a user's Line Manager when a reminder is dispatched. Supports both internal Moodle manager accounts (triggering their Moodle Bell) and external email fallbacks.
* **Executive Command Center:** A polished dashboard featuring Moodle-native KPI line charts, top rule progress bars, and quick-access metrics.
* **Comprehensive Audit Logging:** Tracks every dispatched notification. Includes a dedicated, searchable audit log UI with one-click exports to CSV and Microsoft Excel.
* **Advanced Rule Engine:** 
  * **Recurrence (Nag Mode):** Send notifications repeatedly every *X* days until the user completes the course.
  * **Smart Completion Checking:** Automatically skips users who have already met the course completion criteria.
  * **Rule Duplication:** Clone existing complex rules with a single click to save time.

## 🚀 Installation

### Method 1: Git Installation (Recommended)
1. Navigate to your Moodle `local` directory:
   ```bash
   cd /path/to/moodle/local
   ```
2. Clone the repository:
   ```bash
   git clone git@github.com:muhammadshahrukh285-coder/moodle-local_trainingreminder.git trainingreminder
   ```
3. Run the Moodle upgrade process from the web interface or CLI:
   ```bash
   php admin/cli/upgrade.php
   ```

### Method 2: Zip Installation
1. Download the latest release `.zip` file from GitHub.
2. Log into Moodle as an Administrator.
3. Go to **Site administration > Plugins > Install plugins**.
4. Upload the `.zip` file and select the plugin type as `Local plugin (local)`.
5. Follow the on-screen prompts to complete the database upgrade.

## ⚙️ Configuration

Once installed, navigate to **Site administration > Plugins > Local plugins > Training Reminders**.

1. **Brand Color:** Set your corporate hex color (e.g., `#0f6cbf`) to style the notification emails and dashboard UI.
2. **Logo URL:** Provide a direct URL to your company logo for email headers.
3. **Manager Profile Field:** Enter the exact "Short name" of the custom user profile field (e.g., `line_manager_email`) that contains the manager's email address to enable the Escalation Engine.

## 🛠️ Usage

1. Go to the **Training Reminders Dashboard** via the Site Administration menu.
2. Click **Create New Rule**.
3. Define the **Target** (Specific Courses or Categories).
4. Set the **Trigger:** How many days after enrollment should the reminder fire?
5. Configure **Recurrence:** Should this repeat periodically? 
6. Write your message using dynamic placeholders like `{firstname}` and `{coursetable}`.
7. The background Cron task (`\local_trainingreminder\task\send_reminders`) handles the rest automatically.
