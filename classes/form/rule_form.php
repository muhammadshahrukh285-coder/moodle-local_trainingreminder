<?php
/**
 * Form definition for creating and editing training reminder rules.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainingreminder\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class rule_form extends \moodleform {
    public function definition() {
        global $DB, $CFG;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'generalhdr', get_string('generalsettings', 'local_trainingreminder'));
        $mform->addElement('select', 'targettype', get_string('targettype', 'local_trainingreminder'), [
            'all' => get_string('targettype_all', 'local_trainingreminder'),
            'categories' => get_string('targettype_categories', 'local_trainingreminder'),
            'courses' => get_string('targettype_courses', 'local_trainingreminder')
        ]);
        $mform->setDefault('targettype', 'courses');

        $categories = $DB->get_records_menu('course_categories', null, 'name ASC', 'id, name');
        $mform->addElement('autocomplete', 'categoryids', get_string('selectcategories', 'local_trainingreminder'), $categories, ['multiple' => true]);
        $mform->hideIf('categoryids', 'targettype', 'neq', 'categories');

        $courses = $DB->get_records_menu('course', null, 'fullname ASC', 'id, fullname');
        $mform->addElement('autocomplete', 'courseids', get_string('selectcourses', 'local_trainingreminder'), $courses, ['multiple' => true]);
        $mform->hideIf('courseids', 'targettype', 'neq', 'courses');

        $mform->addElement('text', 'reminderdays', get_string('reminderdays', 'local_trainingreminder'), ['size' => '5']);
        $mform->setType('reminderdays', PARAM_INT);
        $mform->setDefault('reminderdays', 7);
        $mform->addRule('reminderdays', null, 'required', null, 'client');
        $mform->addRule('reminderdays', null, 'numeric', null, 'client');

        $mform->addElement('header', 'emailhdr', get_string('emailsettings', 'local_trainingreminder'));
        
        $mform->addElement('text', 'subject', get_string('subject', 'local_trainingreminder'), ['size' => '60']);
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required', null, 'client');
        $mform->setDefault('subject', get_string('default_subject', 'local_trainingreminder'));

        $mform->addElement('static', 'placeholder_info', '', get_string('placeholders_help', 'local_trainingreminder'));

        $mform->addElement('editor', 'message_editor', get_string('message', 'local_trainingreminder'));
        $mform->setType('message_editor', PARAM_RAW);
        $mform->addRule('message_editor', null, 'required', null, 'client');
        
        $mform->setDefault('message_editor', [
            'text' => get_string('default_message', 'local_trainingreminder'),
            'format' => FORMAT_HTML
        ]);

	// --- SECTION: Schedule Settings Header ---
        $mform->addElement('header', 'schedulehdr', get_string('settingshdr', 'local_trainingreminder'));

        // Reminder Days
        $mform->addElement('text', 'reminderdays', get_string('reminderdays', 'local_trainingreminder'), ['size' => '5']);
        $mform->setType('reminderdays', PARAM_INT);
        $mform->setDefault('reminderdays', 7);
        $mform->addRule('reminderdays', null, 'required', null, 'client');
        $mform->addRule('reminderdays', null, 'numeric', null, 'client');

        // Recurring Checkbox
        $mform->addElement('advcheckbox', 'recurring', get_string('recurring', 'local_trainingreminder'));
        $mform->setDefault('recurring', 0);
        $mform->addHelpButton('recurring', 'recurring', 'local_trainingreminder');

        // Frequency Days
        $mform->addElement('text', 'frequencydays', get_string('frequencydays', 'local_trainingreminder'), ['size' => '5']);
        $mform->setType('frequencydays', PARAM_INT);
        $mform->setDefault('frequencydays', 3);
        $mform->addHelpButton('frequencydays', 'frequencydays', 'local_trainingreminder');
        $mform->hideIf('frequencydays', 'recurring', 'eq', 0);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
