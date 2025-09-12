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
use backup;
use backup_final_element;
use customfield_sprogramme\local\backup\restore_customfield_sprogramme_step;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\output\formfield;
use ReflectionClass;

/**
 * Class data
 *
 * @package     customfield_sprogramme
 * @copyright   2024 CALL Learning <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller extends \core_customfield\data_controller {

    /** @var programme_manager|null $programmemanger The programme manager instance. */
    protected ?programme_manager $programmemanger = null;

    /**
     * data_controller constructor.
     *
     * @param int $id
     * @param \stdClass|null $record
     */
    public function __construct(int $id, \stdClass $record) {
        parent::__construct($id, $record);
        $this->programmemanger = new programme_manager($this->data->get('id'), $this);
    }


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
        if (!$this->data->get('id')) {
            // If the data instance is not yet created, we cannot display the programme.
            return;
        }
        $formfield = new formfield($this->data->get('id'));
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
        if (!$this->data->get('id')) {
            return null;
        }
        $programme = new \customfield_sprogramme\output\programme($this->data->get('id'));
        $renderer = $PAGE->get_renderer('customfield_sprogramme');
        $editbutton = '';
        if ($PAGE->user_is_editing()) {
            $formfield = new formfield($this->data->get('id'));
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
        // Note here: we don't backup RFC or notifications. Competences and disciplines are backed up.
        // But the case where the competence or discipline is deleted in the system is not handled or tested well yet.
        $sprogrammes = new \backup_nested_element('sprogrammes');
        $sprogramme = new \backup_nested_element(
            'sprogramme',
            ['id'],
            [
                'datafieldid',
                'moduleid',
                'sortorder',
                'uc',
                'cct_ept',
                'dd_rse',
                'type_ae',
                'sequence',
                'intitule_seance',
                'cm',
                'td',
                'tp',
                'tpa',
                'tc',
                'aas',
                'fmp',
                'perso_av',
                'perso_ap',
                'consignes',
                'supports',
                'usermodified',
                'timecreated',
                'timemodified',
            ]
        );
        $modules = new \backup_nested_element('modules');
        $module = new \backup_nested_element(
            'module',
            ['id'],
            [
                'datafieldid',
                'sortorder',
                'name',
                'usermodified',
                'timecreated',
                'timemodified',
            ]
        );

        $competencies = new \backup_nested_element('competencies');
        $competency = new \backup_nested_element(
            'competency',
            ['id'],
            [
                'pid',
                'cid',
                'percentage',
                'usermodified',
                'timecreated',
                'timemodified',
            ]
        );
        $disciplines = new \backup_nested_element('disciplines');
        $discipline = new \backup_nested_element(
            'discipline',
            ['id'],
            [
                'pid',
                'did',
                'percentage',
                'usermodified',
                'timecreated',
                'timemodified',
            ]
        );
        $disciplinelist = new \backup_nested_element('disciplinelist');
        $disciplineinfo = new \backup_nested_element(
            'disciplineinfo',
            ['id'],
            [
                'uniqueid',
                'type',
                'parent',
                'name',
                'sortorder',
                'usermodified',
                'timecreated',
                'timemodified',
            ],
        );

        $competencylist = new \backup_nested_element('competencylist');
        $competencyinfo = new \backup_nested_element(
            'competencyinfo',
            ['id'],
            [
                'uniqueid',
                'type',
                'parent',
                'name',
                'sortorder',
                'usermodified',
                'timecreated',
                'timemodified',
            ],
        );

        // Build the tree.
        // Check first if tree is not already built.
        if ($customfieldelement->get_child('sprogrammes')) {
            return;
        }
        // First add the list.
        $courseelement = $customfieldelement->get_parent()->get_parent();
        $courseelement->add_child($disciplinelist);
        $disciplinelist->add_child($disciplineinfo);
        $courseelement->add_child($competencylist);
        $competencylist->add_child($competencyinfo);

        $customfieldelement->add_child($modules);
        $modules->add_child($module);

        $customfieldelement->add_child($sprogrammes);
        $sprogrammes->add_child($sprogramme);
        $sprogramme->add_child($competencies);
        $competencies->add_child($competency);
        $sprogramme->add_child($disciplines);
        $disciplines->add_child($discipline);
        // Define sources.
        $module->set_source_table('customfield_sprogramme_module', ['datafieldid' => backup::VAR_PARENTID]);
        $sprogramme->set_source_table('customfield_sprogramme', ['datafieldid' => backup::VAR_PARENTID]);
        $competency->set_source_table('customfield_sprogramme_competencies', ['pid' => backup::VAR_PARENTID]);
        $discipline->set_source_table('customfield_sprogramme_disc', ['pid' => backup::VAR_PARENTID]);
        $competencyinfo->set_source_table('customfield_sprogramme_complist', []);
        $disciplineinfo->set_source_table('customfield_sprogramme_disclist', []);
        // Annotate ids.
        $sprogramme->annotate_ids('user', 'usermodified');
        $sprogramme->annotate_ids('module', 'moduleid');
        $sprogramme->annotate_ids('customfield_data', 'datafieldid');
        $module->annotate_ids('user', 'usermodified');
        $module->annotate_ids('customfield_data', 'datafieldid');

        $competency->annotate_ids('competencyinfo', 'cid');
        $competency->annotate_ids('sprogramme', 'pid');
        $discipline->annotate_ids('disciplineinfo', 'did');
        $discipline->annotate_ids('sprogramme', 'pid');
        $competency->annotate_ids('user', 'usermodified');
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
        $task = $step->get_task();
        // Here this is a bit of a hack, but we need to add our step at the end of the plans and as a new
        // step of the last task. So we use reflection to get the plan and the tasks.
        // This way it will be executed once after all core steps.
        $reflectiontask = new ReflectionClass($task);
        $property = $reflectiontask->getProperty('plan');
        $property->setAccessible(true);
        $plan = $property->getValue($task);
        $tasks = $plan->get_tasks();
        $endtask = end($tasks);
        if (!$endtask) {
            return;
        }
        $steps = $endtask->get_steps();
        foreach ($steps as $astep) {
            if ($astep->get_name() === 'sprogrammes_structure') {
                // We already added it.
                $astep->set_mapping('customfield', $oldid, $newid); // Mapping the customfield id.
                return;
            }
        }
        $restorestep = new restore_customfield_sprogramme_step('sprogrammes_structure', 'course/course.xml');
        $endtask->add_step($restorestep);
        $restorestep->set_mapping('customfield', $oldid, $newid);
    }

    /**
     * Get the programme data for the current instance.
     *
     * @return array The programme data array.
     */
    public function get_programme_data(): array {
        return  $this->get_programme_manager()->get_data() ?? [];
    }

    /**
     * Get column structure for the current instance.
     *
     * @return array The column structure array.
     */
    public function get_column_structure(): array {
        return $this->get_programme_manager()->get_column_structure() ?? [];
    }
    /**
     * Get the column totals for the current instance.
     *
     * @return array The column totals array.
     */
    public function get_column_totals(): array {
        $columns = $this->get_column_structure();
        $data = $this->get_programme_data();
        return  $this->get_programme_manager()->get_column_totals($data, $columns);
    }

    /**
     * Get the sum of numeric columns for the current instance.
     *
     * @return array The sums of numeric columns.
     */
    public function get_sum(): array {
        $numericcomlumns = programme_manager::get_numeric_columns();
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

    /**
     * Get or create the programme manager for the current instance.
     *
     * We cannot call it in the constructor because programme manager needs the datacontroller also.
     * @return programme_manager The programme manager instance.
     */
    private function get_programme_manager(): programme_manager {
        return $this->programmemanger;
    }

    /**
     * Delete data
     *
     * @return bool
     */
    public function delete() {
        parent::delete();
        $this->get_programme_manager()->delete_programme();
    }
}
