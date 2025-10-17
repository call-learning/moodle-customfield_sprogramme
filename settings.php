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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Setting for the customfield_sprogramme plugin.
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        $emailsenabled = new admin_setting_configcheckbox(
            'customfield_sprogramme/emailsenabled',
            get_string('emailsenabled', 'customfield_sprogramme'),
            get_string('emailsenabled_desc', 'customfield_sprogramme'),
            0,
        );
        $settings->add($emailsenabled);
        $approvalemail = new admin_setting_configtext(
            'customfield_sprogramme/approvalemail',
            get_string('approvalemail', 'customfield_sprogramme'),
            get_string('approvalemail_desc', 'customfield_sprogramme'),
            '',
            PARAM_RAW,
        );
        $settings->add($approvalemail);
    }
}
