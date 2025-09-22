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

use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_module;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\rfc_manager;

/**
 * Sprogramme customfield data generator.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David - CALL Learning <laurent@¢all-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfield_sprogramme_generator extends component_generator_base {
    /**
     * Create a new discipline entry.
     *
     * @param array $record
     * @return stdClass
     */
    public function create_discipline(array $record): stdClass {
        global $DB;
        static $i = 0;
        $i++;
        if (empty($record['type'])) {
            $record['type'] = 'discipline';
        }
        if (empty($record['name'])) {
            $record['name'] = "Discipline $i";
        }
        if (!isset($record['uniqueid'])) {
            $maxid = $DB->get_field_sql('SELECT MAX(uniqueid) FROM {customfield_sprogramme_disclist}');
            $record['uniqueid'] = $maxid + 1;
        }
        if (!isset($record['parent'])) {
            $record['parent'] = null;
        }
        if (!isset($record['sortorder'])) {
            $maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {customfield_sprogramme_disclist} WHERE type = ?', [$record['type']]);
            $record['sortorder'] = $maxsortorder + 1;
        }

        $discipline = new \customfield_sprogramme\sprogramme_disclist();
        $discipline->set('uniqueid', intval($record['uniqueid']));
        $discipline->set('type', $record['type']);
        $discipline->set('parent', $record['parent']);
        $discipline->set('name', $record['name']);
        $discipline->set('sortorder', intval($record['sortorder']));
        $discipline->save();

        return $DB->get_record('customfield_sprogramme_disclist', ['id' => $discipline->get('id')], '*', MUST_EXIST);
    }

    /**
     * Create a new competency entry.
     *
     * @param array $record
     * @return stdClass
     */
    public function create_competency(array $record): stdClass {
        global $DB;
        static $i = 0;
        $i++;
        if (empty($record['type'])) {
            $record['type'] = 'competency';
        }
        if (empty($record['name'])) {
            $record['name'] = "Competency $i";
        }
        if (!isset($record['uniqueid'])) {
            $maxid = $DB->get_field_sql('SELECT MAX(uniqueid) FROM {customfield_sprogramme_complist}');
            $record['uniqueid'] = $maxid + 1;
        }
        if (!isset($record['parent'])) {
            $record['parent'] = null;
        }
        if (!isset($record['sortorder'])) {
            $maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {customfield_sprogramme_complist} WHERE type = ?', [$record['type']]);
            $record['sortorder'] = $maxsortorder + 1;
        }

        $competency = new \customfield_sprogramme\sprogramme_complist();
        $competency->set('uniqueid', intval($record['uniqueid']));
        $competency->set('type', $record['type']);
        $competency->set('parent', $record['parent']);
        $competency->set('name', $record['name']);
        $competency->set('sortorder', intval($record['sortorder']));
        $competency->save();

        return $DB->get_record('customfield_sprogramme_complist', ['id' => $competency->get('id')], '*', MUST_EXIST);
    }

    /**
     * Assign disciplines to a sprogramme field.
     *
     * @param int $fieldid
     * @param array $disciplineids
     * @return void
     */
    public function assign_disciplines(int $fieldid, array $disciplineids): void {
        global $DB;
        foreach ($disciplineids as $disciplineid) {
            $record = new stdClass();
            $record->fieldid = $fieldid;
            $record->disciplineid = $disciplineid;
            $DB->insert_record('customfield_sprogramme_disc', $record);
        }
    }

    /**
     * Assign competencies to a sprogramme field.
     *
     * @param int $fieldid
     * @param array $competencyids
     * @return void
     */
    public function assign_competencies(int $fieldid, array $competencyids): void {
        global $DB;
        foreach ($competencyids as $competencyid) {
            $record = new stdClass();
            $record->fieldid = $fieldid;
            $record->competencyid = $competencyid;
            $DB->insert_record('customfield_sprogramme_comp', $record);
        }
    }

    /**
     * Create a new RFC setting for a sprogramme field.
     *
     * @param int $datafieldid  the data field id
     * @param int $userid the user id (default to admin)
     * @param int $type the type of rfc
     * @param string $snapshot the snapshot data (json)
     * @return stdClass
     */
    public function create_rfc(
        int $datafieldid,
        int $userid = 0,
        int $type = sprogramme_rfc::RFC_REQUESTED,
        string $snapshot = '{}'
    ): stdClass {
        $rfc = new sprogramme_rfc(0);
        $rfc->set('datafieldid', $datafieldid);
        $rfc->set('type', $type);
        $rfc->set('snapshot', $snapshot);
        $rfc->set('adminid', $userid ?: get_admin()->id);
        $rfc->save();
        return $rfc->to_record();
    }

    public function create_notification(int $fieldid, int $notificationvalue): void {
    }

    /**
     * Create a new programme entry.
     *
     * @param int $fieldid
     * @param array $data
     * @return void
     */
    public function create_programme($fieldid, $data): void {
        $dc = \core_customfield\data_controller::create($fieldid);
        foreach ($data as $moduledata) {
            $rows = $moduledata['rows'];
            $module = new sprogramme_module();
            $module->set('datafieldid', $fieldid);
            $module->set('name', $moduledata['modulename']);
            $module->set('sortorder', $moduledata['modulesortorder']);
            $module->save();
            foreach ($rows as $row) {
                $record = new sprogramme();
                $record->set('uc', $dc->get('instanceid'));
                $record->set('datafieldid', $fieldid);
                $record->set('moduleid', $module->get('id'));
                $record->set('sortorder', $row['sortorder']);
                foreach ($row['cells'] as $cell) {
                    $record->set($cell['column'], $cell['value']);
                }
                $record->save();
                $pid = $record->get('id');
                foreach ($row['disciplines'] as $discipline) {
                    $record = new \customfield_sprogramme\local\persistent\sprogramme_disc();
                    $record->set('pid', $pid);
                    $record->set('did', $discipline['id']);
                    $record->set('percentage', $discipline['percentage']);
                    $record->save();
                }
                foreach ($row['competencies'] as $competencies) {
                    $record = new \customfield_sprogramme\local\persistent\sprogramme_comp();
                    $record->set('pid', $pid);
                    $record->set('cid', $competencies['id']);
                    $record->set('percentage', $competencies['percentage']);
                    $record->save();
                }
            }
        }
    }
}
