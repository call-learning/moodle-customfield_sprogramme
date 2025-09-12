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

namespace customfield_sprogramme;

use core_customfield\data;

/**
 * Class field
 *
 * @package     customfield_sprogramme
 * @copyright   2024 CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Plugin type sprogramme.
     */
    const TYPE = 'sprogramme';

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement(
            'checkbox',
            'configdata[enabledbydefault]',
            get_string('programme:enabledbydefault', 'customfield_sprogramme')
        );
        $mform->setType('configdata[enabledbydefault]', PARAM_BOOL);
        $mform->setDefault('configdata[enabledbydefault]', 1);
    }

    /**
     * Does this custom field type support being used as part of the block_myoverview
     * custom field grouping?
     * @return bool
     */
    public function supports_course_grouping(): bool {
        return false;
    }

    /**
     * Before delete bulk actions
     */
    public function delete(): bool {
        // Delete programme attached to this field.
        $datarecords = data::get_records(['fieldid' => $this->field->get('id')]);
        foreach ($datarecords as $data) {
            $programmemanager = new local\programme_manager($data->get('id'));
            $programmemanager->delete_programme();
        }
        // Cleanup attached data tables.
        return parent::delete();
    }
}
