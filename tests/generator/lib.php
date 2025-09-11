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
use \core_customfield\category_controller;
use \core_customfield\field_controller;
use \core_customfield\api;

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
     * Create a new sprogramme field.
     *
     * @param array|stdClass $record
     * @return stdClass
     */
    public function create_sprogramme($record): stdClass {
        global $DB;

        $record = (object) $record;

        // Create category if not provided.
        if (empty($record->categoryid)) {
            $category = $this->create_category();
            $record->categoryid = $category->get('id');
        } else {
            $category = category_controller::get($record->categoryid);
        }

        // Set default values.
        if (empty($record->name)) {
            static $i = 0;
            $i++;
            $record->name = "Programme $i";
        }
        if (empty($record->description)) {
            $record->description = "Description of {$record->name}";
        }
        if (empty($record->descriptionformat)) {
            $record->descriptionformat = FORMAT_HTML;
        }
        if (empty($record->sortorder)) {
            $maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {customfield_sprogramme} WHERE categoryid = ?', [$record->categoryid]);
            $record->sortorder = $maxsortorder + 1;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }

        // Create field.
        $field = field_controller::create(
            (object) [
                'type' => 'sprogramme',
                'name' => $record->name,
                'categoryid' => $record->categoryid,
                'description' => $record->description,
                'descriptionformat' => $record->descriptionformat,
                'sortorder' => $record->sortorder,
                'configdata' => [],
                'timecreated' => $record->timecreated,
                'timemodified' => $record->timemodified,
            ]
        );

        return api::get_instance_fields_data([$field], $field->get('id'));
    }

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

    public function create_sprogramme_disciplines(int $fieldid, array $disciplineids): void {
        global $DB;
        foreach ($disciplineids as $disciplineid) {
            $record = new stdClass();
            $record->fieldid = $fieldid;
            $record->disciplineid = $disciplineid;
            $DB->insert_record('customfield_sprogramme_disc', $record);
        }
    }

    public function create_sprogramme_competencies(int $fieldid, array $competencyids): void {
        global $DB;
        foreach ($competencyids as $competencyid) {
            $record = new stdClass();
            $record->fieldid = $fieldid;
            $record->competencyid = $competencyid;
            $DB->insert_record('customfield_sprogramme_comp', $record);
        }
    }

    public function create_sprogrammme_rfc(int $fieldid, int $rfcvalue): void {
        global $DB;
        $record = new stdClass();
        $record->fieldid = $fieldid;
        $record->rfc = $rfcvalue;
        $DB->insert_record('customfield_sprogramme_rfc', $record);
    }

    public function create_sprogramme_notification(int $fieldid, int $notificationvalue): void {
        global $DB;
        $record = new stdClass();
        $record->fieldid = $fieldid;
        $record->notification = $notificationvalue;
        $DB->insert_record('customfield_sprogramme_notif', $record);
    }
}