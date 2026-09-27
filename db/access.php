<?php
/**
 * Capability definitions for the local_trainingreminder plugin.
 *
 * @package    local_trainingreminder
 * @copyright  2026 Muhammad Shahrukh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/trainingreminder:manage' => [
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ]
];
