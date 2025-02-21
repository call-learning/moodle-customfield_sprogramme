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

namespace customfield_sprogramme\local\api;

use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_disc;
use customfield_sprogramme\local\persistent\sprogramme_comp;
use customfield_sprogramme\local\persistent\sprogramme_module;
require_once($CFG->libdir . '/csvlib.class.php');
use context_course;
use xmldb_structure;
/**
 * Class programme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme {
    /**
     * Get the table structure for the custom field
     * @return array $table
     */
    public static function get_table_structure(): array {
        $columns = [
            [
                'column' => 'cct_ept',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => true,
                'label' => 'CCT / EPT',
                'columnid' => 6,
                'length' => 20,
                'field' => 'select',
                'sample_value' => '',
                'options' => [
                    [
                        'name' => 'Autre',
                        'selected' => false,
                    ],
                    [
                        'name' => 'CCT',
                        'selected' => false,
                    ],
                    [
                        'name' => 'EPT',
                        'selected' => false,
                    ],
                ],
            ],
            [
                'column' => 'dd_rse',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => false,
                'label' => 'DD / RSE',
                'columnid' => 7,
                'length' => 20,
                'field' => 'select',
                'sample_value' => '',
                'options' => [
                    [
                        'name' => 'Autre',
                        'selected' => false,
                    ],
                    [
                        'name' => 'Sans lien avec DD/RSE',
                        'selected' => false,
                    ],
                    [
                        'name' => 'DD/RSE',
                        'selected' => false,
                    ],
                ],
            ],
            [
                'column' => 'type_ae',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => false,
                'label' => 'Type AEEEV',
                'columnid' => 8,
                'length' => 10,
                'field' => 'select',
                'sample_value' => 'TC',
                'options' => [
                    [
                        'name' => 'CM',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TD',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TP',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TPa',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TC',
                        'selected' => false,
                    ],
                    [
                        'name' => 'AAS',
                        'selected' => false,
                    ],
                    [
                        'name' => 'FMP',
                        'selected' => false,
                    ],
                ],
            ],
            [
                'column' => 'intitule_seance',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => 'Intitulé de la séance / de l’exercice',
                'columnid' => 19,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
            ],
            [
                'column' => 'cm',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => true,
                'label' => 'CM',
                'columnid' => 20,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'td',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'TD',
                'columnid' => 21,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'tp',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'TP',
                'columnid' => 22,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'tpa',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'TPa',
                'columnid' => 23,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'tc',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'TC',
                'columnid' => 24,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'aas',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'AAS',
                'columnid' => 25,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'fmp',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'FMP',
                'columnid' => 26,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'perso_av',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'Perso av',
                'columnid' => 27,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
            ],
            [
                'column' => 'perso_ap',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'Perso ap',
                'columnid' => 28,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
            ],
            [
                'column' => 'consignes',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'Consignes de travail pour préparer la séance',
                'columnid' => 29,
                'length' => 3000,
                'field' => 'select',
                'sample_value' => '...',
            ],
            [
                'column' => 'supports',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'Supports pédagogiques essentiels',
                'columnid' => 30,
                'length' => 3000,
                'field' => 'select',
                'sample_value' => '...',
            ],
        ];
        return $columns;
    }

    /**
     * Get all disciplines
     *
     * @return array
     */
    public static function get_disciplines(): array {
        $disciplinesjson = '[
            {"id": 1, "number": 2, "name": "Immunology"},
            {"id": 2, "number": 2, "name": "Literacy & data management"},
            {"id": 3, "number": 2, "name": "Microbiology"},
            {"id": 4, "number": 2, "name": "Parasitology"},
            {"id": 5, "number": 2, "name": "Pathology"},
            {"id": 6, "number": 2, "name": "Pharma-cy-cology-cotherapy"},
            {"id": 7, "number": 2, "name": "Physiology"},
            {"id": 8, "number": 2, "name": "Prof. ethics & communication"},
            {"id": 9, "number": 2, "name": "Toxicology"},
            {"id": 10, "number": 3, "name": "CA_EQ Anesthesiology"},
            {"id": 11, "number": 3, "name": "CA_EQ Clinical pract training"},
            {"id": 12, "number": 3, "name": "CA_EQ Diagnostic imaging"},
            {"id": 13, "number": 3, "name": "CA_EQ Diagnostic pathology"},
            {"id": 14, "number": 3, "name": "CA_EQ Infectious diseases"},
            {"id": 15, "number": 3, "name": "CA_EQ Medecine"},
            {"id": 16, "number": 3, "name": "CA_EQ Preventive medicine"},
            {"id": 17, "number": 3, "name": "CA_EQ Repro & obstetrics"},
            {"id": 18, "number": 3, "name": "CA_EQ Surgery"},
            {"id": 19, "number": 3, "name": "CA_EQ Therapy"},
            {"id": 20, "number": 4, "name": "FPA Anesthesiology"},
            {"id": 21, "number": 4, "name": "FPA Clinical pract training"},
            {"id": 22, "number": 4, "name": "FPA Diagnostic imaging"},
            {"id": 23, "number": 4, "name": "FPA Diagnostic pathology"},
            {"id": 24, "number": 4, "name": "FPA Herd health management"},
            {"id": 25, "number": 4, "name": "FPA Husb, breeding & economics"},
            {"id": 26, "number": 4, "name": "FPA Infectious diseases"},
            {"id": 27, "number": 4, "name": "FPA Medecine"},
            {"id": 28, "number": 4, "name": "FPA Preventive medicine"},
            {"id": 29, "number": 4, "name": "FPA Repro & obstetrics"},
            {"id": 30, "number": 4, "name": "FPA Surgery"},
            {"id": 31, "number": 4, "name": "FPA Therapy"},
            {"id": 32, "number": 5, "name": "Control of food & feed"},
            {"id": 33, "number": 5, "name": "Food hygiene & environ. health"},
            {"id": 34, "number": 5, "name": "Food technology"},
            {"id": 35, "number": 5, "name": "Vet. legis & certification"},
            {"id": 36, "number": 5, "name": "Zoonoses"}
        ]';
        $disciplines = json_decode($disciplinesjson, true);
        return $disciplines;
    }

    /**
     * Get all competences
     * @return array
     */
    public static function get_competencies(): array {
        // Sorted by name, updates the ID too
        $competencesjson = '[
            {"id": 1, "number": 1, "name": "Agir de manière responsable"},
            {"id": 2, "number": 1, "name": "Agir en Scientifique"},
            {"id": 3, "number": 1, "name": "Agir pour la santé publique"},
            {"id": 4, "number": 1, "name": "Communiquer"},
            {"id": 5, "number": 1, "name": "Conseiller et Prévenir"},
            {"id": 6, "number": 1, "name": "Etablir un diagnostic"},
            {"id": 7, "number": 1, "name": "Soigner et traiter"},
            {"id": 8, "number": 1, "name": "Travailler en entrepris"}
        ]';
        $competences = json_decode($competencesjson, true);
        return $competences;
    }

    /**
     * Get the column structure for the custom field
     * @param int $courseid
     * @return array $columns
     */
    public static function get_column_structure($courseid): array {
        $table = self::get_table_structure();
        $canedit = has_capability('customfield/sprogramme:edit', context_course::instance($courseid));
        $table = array_map(function($column) use ($canedit) {
            $column['canedit'] = $canedit;
            return $column;
        }, $table);
        return array_values($table);
    }

    /**
     * Get the data for a given course
     * @param int $courseid
     * @return array $data
     */
    public static function get_data(int $courseid): array {
        $modules = sprogramme_module::get_all_records_for_course($courseid);
        $columns = self::get_column_structure($courseid);
        $data = [];
        foreach ($modules as $module) {
            $records = sprogramme::get_all_records_for_module($module->get('id'));
            $modulerows = [];
            foreach ($records as $record) {
                $row = [];
                foreach ($columns as $key => $column) {
                    $row[] = [
                        'column' => $column['column'],
                        'value' => $record->get($column['column']),
                        'type' => $column['type'],
                        'visible' => $column['visible'],
                    ];
                }
                $disciplines = sprogramme_disc::get_all_records_for_programme($record->get('id'));
                $disciplinedata = [];
                foreach ($disciplines as $discipline) {
                    $disciplinedata[] = [
                        'id' => $discipline->get('did'),
                        'name' => $discipline->get('discipline'),
                        'percentage' => $discipline->get('percentage'),
                    ];
                }
                $competencies = sprogramme_comp::get_all_records_for_programme($record->get('id'));
                $competencydata = [];
                foreach ($competencies as $competency) {
                    $competencydata[] = [
                        'id' => $competency->get('cid'),
                        'name' => $competency->get('competency'),
                        'percentage' => $competency->get('percentage'),
                    ];
                }
                $modulerows[] = [
                    'id' => $record->get('id'),
                    'sortorder' => $record->get('sortorder'),
                    'cells' => $row,
                    'disciplines' => $disciplinedata,
                    'competencies' => $competencydata,
                ];
            }
            $data[] = [
                'moduleid' => $module->get('id'),
                'modulename' => $module->get('name'),
                'modulesortorder' => $module->get('sortorder'),
                'rows' => $modulerows,
                'columns' => $columns,
            ];
        }
        return $data;
    }

    /**
     * Set the data.
     * @param int $courseid
     * @param array $data
     */
    public static function set_records(int $courseid, array $data): void {
        foreach ($data as $module) {
            $moduleid = $module['id'];
            $rows = $module['rows'];
            $mod = sprogramme_module::get_record(['id' => $moduleid]);
            $mod->set('name', $module['name']);
            $mod->set('sortorder', $module['sortorder']);
            $mod->save();
            $records = sprogramme::get_all_records_for_module($moduleid);
            foreach ($rows as $row) {
                $updated = false;
                foreach ($records as $record) {
                    if ($record->get('id') == $row['id']) {
                        self::update_record($record, $row, $courseid);
                        $updated = true;
                    }
                }
                if (!$updated) {
                    $record = new sprogramme();
                    $record->set('uc', $courseid);
                    $record->set('courseid', $courseid);
                    $record->set('moduleid', $moduleid);
                    self::update_record($record, $row, $courseid);
                }
            }
        }

    }

    /**
     * Update a record
     * @param sprogramme $record
     * @param array $row
     * @param int $courseid
     */
    private static function update_record(sprogramme $record, array $row, int $courseid): void {
        $columns = self::get_column_structure($courseid);
        $fields = array_map(function($column) {
            return $column['column'];
        }, $columns);
        foreach ($fields as $field) {
            if (!isset($row['cells'])) {
                continue;
            }
            foreach ($row['cells'] as $cell) {
                if ($cell['column'] == $field) {
                    if ($cell['type'] == PARAM_INT) {
                        $value = $cell['value'] ? (int)$cell['value'] : null;
                        $record->set($field, $value);
                        continue;
                    } else if ($cell['type'] == PARAM_FLOAT) {
                        $value = $cell['value'] ? (float)$cell['value'] : null;
                        $record->set($field, $value);
                        continue;
                    } else {
                        $record->set($field, $cell['value']);
                    }
                }
            }
        }
        $record->save();
        self::set_disciplines($record, $row);
        self::set_competencies($record, $row);
    }

    /**
     * Set the disciplines
     * @param sprogramme $record
     * @param array $row
     */
    public static function set_disciplines(sprogramme $record, array $row): void {
        if (!isset($row['disciplines']) || !is_array($row['disciplines'])) {
            return;
        }
        $disciplines = $row['disciplines'];

        $existing = sprogramme_disc::get_all_records_for_programme($row['id']);
        foreach ($disciplines as $discipline) {
            $updated = false;
            foreach ($existing as $record) {
                if ($record->get('did') == $discipline['id']) {
                    $record->set('percentage', $discipline['percentage']);
                    $record->save();
                    $updated = true;
                }
            }
            if (!$updated) {
                $record = new sprogramme_disc();
                $record->set('pid', $row['id']);
                $record->set('did', $discipline['id']);
                $record->set('discipline', $discipline['name']);
                $record->set('percentage', $discipline['percentage']);
                $record->save();
            }
        }
        // Remove any disciplines that are no longer there.
        foreach ($existing as $record) {
            $found = false;
            foreach ($disciplines as $discipline) {
                if ($record->get('did') == $discipline['id']) {
                    $found = true;
                }
            }
            if (!$found) {
                $record->delete();
            }
        }
    }

    /**
     * Set the competencies
     * @param sprogramme $record
     * @param array $row
     */
    public static function set_competencies(sprogramme $record, array $row): void {
        if (!isset($row['competencies']) || !is_array($row['competencies'])) {
            return;
        }
        $competencies = $row['competencies'];

        $existing = sprogramme_comp::get_all_records_for_programme($row['id']);
        foreach ($competencies as $competency) {
            $updated = false;
            foreach ($existing as $record) {
                if ($record->get('cid') == $competency['id']) {
                    $record->set('percentage', $competency['percentage']);
                    $record->save();
                    $updated = true;
                }
            }
            if (!$updated) {
                $record = new sprogramme_comp();
                $record->set('pid', $row['id']);
                $record->set('cid', $competency['id']);
                $record->set('competency', $competency['name']);
                $record->set('percentage', $competency['percentage']);
                $record->save();
            }
        }
        // Remove any competencies that are no longer there.
        foreach ($existing as $record) {
            $found = false;
            foreach ($competencies as $competency) {
                if ($record->get('cid') == $competency['id']) {
                    $found = true;
                }
            }
            if (!$found) {
                $record->delete();
            }
        }
    }

    /**
     * Get or create a module
     * @param string $name
     * @param int $courseid
     * @param int $sortorder
     * @return int $moduleid
     */
    public static function get_or_create_module($name, $courseid, $sortorder): int {
        $module = sprogramme_module::get_record(['courseid' => $courseid, 'name' => $name]);
        if ($module) {
            return $module->get('id');
        }
        return self::create_module($name, $courseid, $sortorder);
    }

    /**
     * Create a new module
     * @param string $name
     * @param int $courseid
     * @param int $sortorder
     * @return int $moduleid
     */
    public static function create_module($name, $courseid, $sortorder): int {
        $module = new sprogramme_module();
        $module->set('courseid', $courseid);
        $module->set('name', $name);
        $module->set('sortorder', $sortorder);
        $module->save();
        return $module->get('id');
    }

    /**
     * Delete a programme for a given course
     * @param int $courseid
     */
    public static function delete_programme($courseid): void {
        $modules = sprogramme_module::get_all_records_for_course($courseid);
        foreach ($modules as $module) {
            self::delete_module($courseid, $module->get('id'));
        }
    }

    /**
     * Delete a module
     * @param int $courseid
     * @param int $moduleid
     * return bool
     */
    public static function delete_module($courseid, $moduleid): bool {
        $module = sprogramme_module::get_record(['id' => $moduleid]);
        if ($module->get('courseid') == $courseid) {
            // Delete all rows in this module.
            $records = sprogramme::get_all_records_for_module($moduleid);
            foreach ($records as $record) {
                $record->delete();
            }
            $module->delete();
            return true;
        }
        return false;
    }

    /**
     * Create a new row
     * @param int $courseid
     * @param int $moduleid
     * @param int $prevrowid
     * @return int $sortorder
     */
    public static function create_row($courseid, $moduleid, $prevrowid): int {
        if (!$moduleid) {
            $moduleid = self::get_or_create_module('Module', $courseid, 0);
        }
        $record = new sprogramme();
        $record->set('uc', $courseid);
        $record->set('moduleid', $moduleid);
        $record->set('courseid', $courseid);
        $record->set('sortorder', 0);
        // Set all other fields to null or ''.
        $fields = self::get_table_structure();
        foreach ($fields as $field) {
            if ($field['field'] == 'int') {
                $record->set($field['column'], null);
            } else if ($field['field'] == 'float') {
                $record->set($field['column'], null);
            } else {
                $record->set($field['column'], '');
            }
        }
        $record->save();
        self::update_sort_order('row', $moduleid, $record->get('id'), $prevrowid);
        return $record->get('id');
    }

    /**
     * Delete a row
     * @param int $courseid
     * @param int $rowid
     * @return bool
     */
    public static function delete_row($courseid, $rowid): bool {
        $record = sprogramme::get_record(['id' => $rowid]);
        if ($record->get('courseid') == $courseid) {
            $record->delete();
            return true;
        }
        return false;
    }

    /**
     * Update the sort order
     * @param string $type
     * @param int $moduleid
     * @param int $id
     * @param int $previd
     */
    public static function update_sort_order($type, $moduleid, $id, $previd): void {
        if ($type == 'row') {
            $newrecord = sprogramme::get_record(['id' => $id]);
            // In case the row is moved to the top.
            if (!$previd) {
                $newrecord->set('sortorder', 0);
                $newrecord->set('moduleid', $moduleid);
                $newrecord->save();
                $records = sprogramme::get_all_records_for_module($moduleid);
                $sortorder = 0;
                foreach ($records as $record) {
                    if ($record->get('id') == $id) {
                        continue;
                    }
                    $sortorder++;
                    $record->set('sortorder', $sortorder);
                    $record->save();
                }
                return;
            }
            $prevrecord = sprogramme::get_record(['id' => $previd]);
            $records = sprogramme::get_all_records_for_module($prevrecord->get('moduleid'));
            $sortorder = 0;
            foreach ($records as $record) {
                if ($record->get('id') == $id) {
                    continue;
                }
                if ($record->get('id') == $previd) {
                    $sortorder = $record->get('sortorder');
                    // Update the remaining records, depending on the action.
                    $sortorder++;
                    $newrecord->set('sortorder', $sortorder);
                    $newrecord->set('moduleid', $moduleid);
                    $newrecord->save();
                    continue;
                }
                if ($sortorder) {
                    $sortorder++;
                    $record->set('sortorder', $sortorder);
                    $record->save();
                }
            }
        }
    }

    /**
     * Get the data in csv format
     * @param int $courseid
     * @return string $csv
     */
    public static function get_csv_data(int $courseid): string {
        $data = self::get_data($courseid);
        $csvexport = new \csv_export_writer('comma', '"');
        $course = get_course($courseid);
        $filename = 'programme_' . $course->shortname . '_' . date('Ymd_His') . '.txt';
        $csvexport->set_filename($filename);
        $columns = self::get_column_structure($courseid);
        // Add the module name to the first item of the columns.
        $columns = array_merge([['column' => 'module']], $columns, 
            [['column' => 'disciplines'], ['column' => 'competencies']]);
        $csvexport->add_data(array_map(function($column) {
            return $column['column'];
        }, $columns));
        foreach($data as $module) {
            $name = $module['modulename'];
            foreach ($module['rows'] as $row) {
                $cells = [];
                foreach ($row['cells'] as $cell) {
                    $cells[] = $cell['value'];
                }
                $disciplines = [];
                foreach ($row['disciplines'] as $discipline) {
                    $disciplines[] = $discipline['name'] . ' (' . $discipline['percentage'] . '%)';
                }
                $competencies = [];
                foreach ($row['competencies'] as $competency) {
                    $competencies[] = $competency['name'] . ' (' . $competency['percentage'] . '%)';
                }
                $cells[] = implode('| ', $disciplines);
                $cells[] = implode('| ', $competencies);
                $csvexport->add_data(array_merge([$name], $cells));
            }
        }
        return $csvexport->print_csv_data(true);
    }
}
