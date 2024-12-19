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
use customfield_sprogramme\output\programme;

/**
 * Class data
 *
 * @package     customfield_sprogramme
 * @copyright   2024 CALL Learning <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {

    /**
     * Return the name of the field where the information is stored
     * @return string
     */
    public function datafield(): string {
        return 'value';
    }

    /**
     * Add fields for editing the programme.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        global $COURSE;
        $url = new \moodle_url('/customfield/field/sprogramme/edit.php',
            [
                'fieldid' => $this->get_field()->get('id'),
                'courseid' => $COURSE->id,
            ]
        );
        $mform->addElement('static', 'customfield_text', get_string('edit', 'customfield_text'),
            \html_writer::link($url, get_string('edit', 'customfield_text')));
        $mform->addElement('textarea', 'value', $this->get_field()->get_formatted_name(), ['rows' => 5, 'cols' => 50]);
        $mform->setType('value', PARAM_RAW);
    }

    /**
     * Validates data for this field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function instance_form_validation(array $data, array $files): array {
        $errors = parent::instance_form_validation($data, $files);
        return $errors;
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return $this->get_field()->get_configdata_property('defaultvalue');
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        global $PAGE;
        $value = $this->get_value();
        $programme = new \customfield_sprogramme\output\programme($value);
        $renderer = $PAGE->get_renderer('customfield_sprogramme');
        return $renderer->render($programme);
    }
}
