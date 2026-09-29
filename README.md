# Training Reminder Automation (`local_trainingreminder`)

[![Moodle CI](https://img.shields.io/badge/Moodle_CI-Passed-success)](#)
[![Version](https://img.shields.io/badge/Version-1.0.3-blue)](#)
[![License: GPL v3](https://img.shields.io/badge/License-GPL_v3-green.svg)](#)

An enterprise-grade Moodle notification engine designed to automate compliance follow-ups, reduce administrative overhead, and drive course completion rates. 

Designed specifically for corporate, healthcare, and institutional environments, this "set-and-forget" plugin handles the heavy lifting of chasing down employees to finish their mandatory training.

---

## 🎯 Core Features

* **Intelligent "Nag Mode" (Recurring Reminders):** Traditional LMS alerts are one-off emails that get buried. Set up recurring rules that automatically loop and resend reminders every few days until the user meets the course completion criteria.
* **Manager Escalation Engine:** If a user continues to lag behind, the system automatically carbon-copies their Line Manager, routing the alert to internal Moodle manager accounts or external email addresses based on user profile fields.
* **Multi-Channel Delivery:** Reminders are pushed natively through Moodle’s Message API. Users receive alerts via the web Bell UI, Moodle Mobile App (Push Notifications), and beautifully formatted HTML emails.
* **Executive Command Center:** A built-in administrator dashboard featuring native Moodle KPI line charts, top rule progress bars, and quick-access delivery metrics.
* **Corporate Branding:** White-label your outgoing emails directly from the settings page by injecting your corporate HEX colors and a custom logo URL.

---

## 📸 Visual Proof

### The Executive Dashboard
<img width="1784" height="738" alt="Reminder 1" src="https://github.com/user-attachments/assets/489c86f1-81a5-4638-bf8d-a2f22a1a2f19" />


### Rule Creation & Nag Mode
<img width="1797" height="396" alt="Reminder 3" src="https://github.com/user-attachments/assets/cfbc5168-4036-4f5e-a194-955d079d18ef" />

---

## 🛡️ Enterprise-Ready & Secure

This plugin is engineered to strict Moodle core standards, ensuring it is safe, compliant, and performant for large-scale corporate deployments:
* **GDPR Compliant:** Includes a complete Privacy API provider (`\privacy\provider`) allowing user notification logs to be exported or erased natively via Moodle's data privacy tools.
* **Secure Architecture:** Built utilizing parameterized database queries, explicit user capabilities (`local/trainingreminder:manage`), and strict `sesskey` validation.
* **Validated Schema:** Fully compliant XMLDB schema structures that have successfully passed the automated Moodle Plugin CI scanner.

---

## ⚙️ Installation

1. Download the latest release from this repository or the Moodle Plugins Directory.
2. Extract the `.zip` file into your Moodle `/local/` directory. (The folder must be named `trainingreminder`).
3. Log in to your Moodle site as an admin and navigate to **Site administration > Notifications** to complete the installation.
4. *Requirement:* Ensure Moodle's standard Scheduled Tasks (Cron) are running frequently to allow background message dispatching.

---

## 💡 Custom Moodle Development & Consulting

This plugin is 100% free and open-source. However, if your organization requires tailored LMS solutions, I specialize in bespoke Moodle development, automated database integration scripts, and custom reporting modules for the corporate and banking sectors.

Whether you need to merge live Moodle databases with historical trackers, build new notification workflows, or develop enterprise plugins from the ground up, I can help optimize your learning environment.

**Available for freelance contracts:**
* 💼 **[Hire me on Upwork](https://www.upwork.com/freelancers/shahrukhmoodle?mp_source=share)**
* 💼 **[Hire me on Fiverr](https://www.fiverr.com/s/yev5Ymb)**
* 🤝 **[Connect with me on LinkedIn](https://www.linkedin.com/in/muhammad-shahrukh-526746111/)** to discuss your project.

---
**Author:** Muhammad Shahrukh  
**Copyright:** © 2026
