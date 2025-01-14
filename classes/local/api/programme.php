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
        $fields = [
            'cct_ept' => [
                'column' => 'cct_ept',
                'type' => PARAM_TEXT,
                'label' => 'CCT / EPT',
                'columnid' => 6,
                'length' => 20,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'dd_rse' => [
                'column' => 'dd_rse',
                'type' => PARAM_TEXT,
                'label' => 'DD / RSE',
                'columnid' => 7,
                'length' => 20,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'type_ae' => [
                'column' => 'type_ae',
                'type' => PARAM_TEXT,
                'label' => 'Type AEEEV',
                'columnid' => 8,
                'length' => 10,
                'field' => 'select',
                'sample_value' => 'TC',
                'active' => true,
            ],
            'sequence' => [
                'column' => 'sequence',
                'type' => PARAM_INT,
                'label' => 'Sequence dans le module',
                'columnid' => 12,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '1',
                'active' => true,
            ],
            'intitule_seance' => [
                'column' => 'intitule_seance',
                'type' => PARAM_TEXT,
                'label' => 'Intitulé de la séance / de l’exercice',
                'columnid' => 19,
                'length' => 255,
                'field' => 'text',
                'sample_value' => 'Travaux cliniques superivsés (5 jours pleins x 8 heures + 2 matinées de we x 4 heures - une demi journée de 4 h)',
                'active' => true,
            ],
            'cm' => [
                'column' => 'cm',
                'type' => PARAM_FLOAT,
                'label' => 'CM',
                'columnid' => 20,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'td' => [
                'column' => 'td',
                'type' => PARAM_FLOAT,
                'label' => 'TD',
                'columnid' => 21,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '10.0',
                'active' => true,
            ],
            'tp' => [
                'column' => 'tp',
                'type' => PARAM_FLOAT,
                'label' => 'TP',
                'columnid' => 22,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'tpa' => [
                'column' => 'tpa',
                'type' => PARAM_FLOAT,
                'label' => 'TPa',
                'columnid' => 23,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'tc' => [
                'column' => 'tc',
                'type' => PARAM_INT,
                'label' => 'TC',
                'columnid' => 24,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '44',
                'active' => true,
            ],
            'aas' => [
                'column' => 'aas',
                'type' => PARAM_FLOAT,
                'label' => 'AAS',
                'columnid' => 25,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '4.0',
                'active' => true,
            ],
            'fmp' => [
                'column' => 'fmp',
                'type' => PARAM_FLOAT,
                'label' => 'FMP',
                'columnid' => 26,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'perso_av' => [
                'column' => 'perso_av',
                'type' => PARAM_FLOAT,
                'label' => 'Perso av',
                'columnid' => 27,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '4.0',
                'active' => true,
            ],
            'perso_ap' => [
                'column' => 'perso_ap',
                'type' => PARAM_FLOAT,
                'label' => 'Perso ap',
                'columnid' => 28,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
            ],
            'consignes' => [
                'column' => 'consignes',
                'type' => PARAM_TEXT,
                'label' => 'Consignes de travail pour préparer la séance',
                'columnid' => 29,
                'length' => 10,
                'field' => 'select',
                'sample_value' => 'Pré-requis. : cours A4 (UC 413, 421)',
                'active' => true,
            ],
            'supports' => [
                'column' => 'supports',
                'type' => PARAM_TEXT,
                'label' => 'Supports pédagogiques essentiels',
                'columnid' => 30,
                'length' => 10,
                'field' => 'select',
                'sample_value' => 'Hopital virtuel (Moodle), jeu sérieux, rondes pédagogiques orales (TD)',
                'active' => true,
            ],
        ];
        return $fields;
    }

    /**
     * Get the column structure for the custom field
     * @return array $columns
     */
    public static function get_column_structure(): array {
        $table = self::get_table_structure();
        return array_values($table);
    }

    /**
     * Get the data for a given course
     * @param int $courseid
     * @return array $data
     */
    public static function get_data(int $courseid): array {
        $records = sprogramme::get_all_records_for_course($courseid);
        $columns = self::get_column_structure();
        $data = [];
        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = [
                    'column' => $column['column'],
                    'value' => $record->get($column['column']),
                    'type' => $column['type'],
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
            $data[] = [
                'id' => $record->get('id'),
                'sortorder' => $record->get('sortorder'),
                'cells' => $row,
                'disciplines' => $disciplinedata,
            ];
        }
        return $data;
    }

    /**
     * Set the rows.
     * @param int $courseid
     * @param array $rows
     */
    public static function set_records(int $courseid, array $rows): void {
        // Get the existing records
        $records = sprogramme::get_all_records_for_course($courseid);
        // Loop through the rows
        foreach($rows as $row) {
            $updated = false;
            foreach ($records as $record) {
                if ($record->get('id') == $row['id']) {
                    self::update_record($record, $row);
                    $updated = true;
                }
            }
            if (!$updated) {
                $record = new sprogramme();
                $record->set('uc', $courseid);
                $record->set('courseid', $courseid);
                $record->set('sortorder', $row['sortorder']);
                self::update_record($record, $row);
            }
        }
    }

    /**
     * Update a record
     * @param sprogramme $record
     * @param array $row
     */
    private static function update_record(sprogramme $record, array $row): void {
        $fields = array_keys(self::get_table_structure());
        foreach ($fields as $field) {
            // Find the row value
            if (!isset($row['cells'])) {
                continue;
            }
            foreach ($row['cells'] as $cell) {
                if ($cell['column'] == $field) {
                    if ($cell['type'] == PARAM_INT) {
                        $value = $cell['value'] ? (int)$cell['value'] : null;
                        $record->set($field, $value);
                        continue;
                    } elseif ($cell['type'] == PARAM_FLOAT) {
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
    }

    /**
     * Set the disciplines
     * @param sprogramme $record
     * @param array $row
     */
    public static function set_disciplines(sprogramme $record, array $row): void {
        if (!isset($row['disciplines']) || !is_array($row['disciplines']) || empty($row['disciplines'])) {
            return;
        }
        $disciplines = $row['disciplines'];
        // Assuming there is a method to set disciplines in the sprogramme class
        // Fetch the existing disciplines
        $existing = sprogramme_disc::get_all_records_for_programme($row['id']);
        foreach($disciplines as $discipline) {
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
    }

    /**
     * Create a new row
     * @param int $courseid
     * @return int $sortorder
     */
    public static function create_row($courseid, $sortorder): int {
        $record = new sprogramme();
        $record->set('uc', $courseid);
        $record->set('courseid', $courseid);
        $record->set('sortorder', $sortorder);
        // Set all other fields to null or ''
        $fields = self::get_table_structure();
        foreach ($fields as $field) {
            if ($field['field'] == 'int') {
                $record->set($field['column'], null);
            } elseif ($field['field'] == 'float') {
                $record->set($field['column'], null);
            } else {
                $record->set($field['column'], '');
            }
        }
        $record->save();
        self::update_sort_order($courseid, 'add', $record->get('id'));
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
            self::update_sort_order($courseid, 'remove', $rowid);
            $record->delete();
            return true;
        }
        return false;
    }

    /**
     * Update the sort order
     * @param int $courseid
     * @param string $actions, add or remove
     * @param int $rowid
     */
    public static function update_sort_order($courseid, $actions, $rowid): void {
        $records = sprogramme::get_all_records_for_course($courseid);
        $sortorder = 0;
        foreach ($records as $record) {
            if ($record->get('id') == $rowid) {
                $sortorder = $record->get('sortorder');
                // Update the remaining records, depending on the action
                if ($actions == 'add') {
                    $sortorder++;
                }
            }
            if ($sortorder) {
                $record->set('sortorder', $sortorder);
                $record->save();
                $sortorder++;
            }
        }
    }
}
