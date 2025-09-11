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
use customfield_sprogramme\local\api\programme;
use customfield_sprogramme\output\formfield;
use customfield_sprogramme\local\api\programme as programme_api;

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
        return 'intvalue';
    }

    /**
     * Add fields for editing the programme.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        global $PAGE;
        $elementname = $this->get_form_element_name();
        $mform->addElement(
            'advcheckbox',
            $elementname,
            get_string('programme:enabled', 'customfield_sprogramme')
        );
        $mform->setDefault($elementname, $this->get_default_value());
        $mform->setType($elementname, PARAM_BOOL);
        $renderer = $PAGE->get_renderer('customfield_sprogramme');
        $formfield = new formfield();
        $mform->addElement('static', 'rendered', $this->get_field()->get('name'), $renderer->render($formfield));
        $mform->setType('rendered', PARAM_RAW);
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return $this->get_field()->get_configdata_property('enabledbydefault') ? 1 : 0;
    }

    /**
     * Returns value in a human-readable format
     * This is exporting the value as seen by the user, not how it is stored in the database.
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        global $PAGE;
        $isenabled = $this->get_value();
        $programme = new \customfield_sprogramme\output\programme($this->data->get('instanceid'));
        $renderer = $PAGE->get_renderer('customfield_sprogramme');
        $editbutton = '';
        if ($PAGE->user_is_editing()) {
            $formfield = new formfield();
            $editbutton = $renderer->render($formfield);
        }
        return $isenabled ? $editbutton . $renderer->render($programme) : '';
    }

    /**
     * Implement the backup callback for the custom field element.
     * This includes any embedded files in the custom field element.
     *
     * @param \backup_nested_element $customfieldelement The custom field element to be backed up.
     */
    public function backup_define_structure(\backup_nested_element $customfieldelement): void {
        $annotations = $customfieldelement->get_file_annotations();

        if (!isset($annotations['customfield_textarea']['value'])) {
            $customfieldelement->annotate_files('customfield_textarea', 'value', 'id');
        }
    }

    /**
     * Implement the restore callback for the custom field element.
     * This includes restoring any embedded files in the custom field element.
     *
     * @param \restore_structure_step $step The restore step instance.
     * @param int $newid The new ID for the custom field data after restore.
     * @param int $oldid The original ID of the custom field data before backup.
     */
    public function restore_define_structure(\restore_structure_step $step, int $newid, int $oldid): void {
        if (!$step->get_mappingid('customfield_data', $oldid)) {
            $step->set_mapping('customfield_data', $oldid, $newid, true);
            $step->add_related_files('customfield_textarea', 'value', 'customfield_data');
        }
    }

    /**
     * Get the programme data for the current instance.
     *
     * @return array The programme data array.
     */
    public function get_programme_data(): array {
        return \customfield_sprogramme\local\api\programme::get_data($this->data->get('instanceid'));
    }

    /**
     * Get column structure for the current instance.
     *
     * @return array The column structure array.
     */
    public function get_column_structure(): array {
        return \customfield_sprogramme\local\api\programme::get_column_structure($this->data->get('instanceid'));
    }
    /**
     * Get the column totals for the current instance.
     *
     * @return array The column totals array.
     */
    public function get_column_totals(): array {
        $columns = $this->get_column_structure();
        $data = $this->get_programme_data();
        return \customfield_sprogramme\local\api\programme::get_column_totals($data, $columns);
    }

    /**
     * Get the sum of numeric columns for the current instance.
     *
     * @return array The sums of numeric columns.
     */
    public function get_sum(): array {
        $numericcomlumns = programme::get_numeric_columns();
        $columnstotals = $this->get_column_totals();
        // Filter out the numeric columns.
        $columns = array_filter($columnstotals, function ($column) use ($numericcomlumns) {
            foreach ($numericcomlumns as $numericcolumn) {
                if ($column['column'] == $numericcolumn['column']) {
                    return true;
                }
            }
            return false;
        });
        return $columns;
    }
}
