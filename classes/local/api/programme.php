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

defined('MOODLE_INTERNAL') || die();

use cache;
use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_disc;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\persistent\sprogramme_comp;
use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_module;
use customfield_sprogramme\local\persistent\sprogramme_change;
use customfield_sprogramme\local\api\notifications;

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
                'group' => '',
            ],
            [
                'column' => 'dd_rse',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => true,
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
                'group' => '',
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
                'group' => '',
            ],
            [
                'column' => 'cm',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => 'CM',
                'columnid' => 20,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => 'unique',
                'sum' => 0,
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
                'group' => '',
                'sum' => 0,
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
                'group' => '',
                'sum' => 0,
            ],
            [
                'column' => 'consignes',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => 'Consignes de travail pour préparer la séance',
                'columnid' => 29,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
                'group' => '',
            ],
            [
                'column' => 'supports',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => 'Supports pédagogiques essentiels',
                'columnid' => 30,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
                'group' => '',
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
        $disciplines = sprogramme_disclist::get_sorted();
        return $disciplines;
    }

    /**
     * Get all competences
     * @return array
     */
    public static function get_competencies(): array {
        $competences = sprogramme_complist::get_sorted();
        return $competences;
    }

    /**
     * Get the column structure for the custom field
     * @param int $courseid
     * @return array $columns
     */
    public static function get_column_structure($courseid): array {
        $table = self::get_table_structure();
        $canedit = has_capability('customfield/sprogramme:editall', context_course::instance($courseid));
        $canaddrfc = self::can_add_rfc($courseid);
        $table = array_map(function($column) use ($canedit, $canaddrfc) {
            if ($column['canedit'] == false) {
                $column['canaddrfc'] = $canaddrfc;
                $column['canedit'] = $canedit;
            }
            return $column;
        }, $table);
        return array_values($table);
    }

    /**
     * Get the user name, userpicture for a given user id
     * @param int $userid
     * @return array
     */
    public static function get_user_info($userid) {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid]);
        if ($user) {
            return [
                'fullname' => fullname($user),
                'userid' => $user->id,
            ];
        }
        return [];
    }

    /**
     * Find a change record for a given column and row
     * @param array $changerecords
     * @param int $rowid
     * @param string $column
     * @return array
     */
    public static function find_change_record($changerecords, $rowid, $column) {
        $changes = [];
        foreach ($changerecords as $changerecord) {
            if ($changerecord instanceof sprogramme_change) {
                if ($changerecord->get('pid') == $rowid && $changerecord->get('field') == $column) {
                    $changes[] = [
                        'oldvalue' => $changerecord->get('oldvalue'),
                        'newvalue' => $changerecord->get('newvalue'),
                        'timemodified' => $changerecord->get('timemodified'),
                        'userinfo' => self::get_user_info($changerecord->get('adminid')),
                    ];
                }
            } else {
                if ($changerecord->pid == $rowid && $changerecord->field == $column) {
                    $changes[] = [
                        'oldvalue' => $changerecord->oldvalue,
                        'newvalue' => $changerecord->newvalue,
                        'timemodified' => $changerecord->timemodified,
                        'userinfo' => self::get_user_info($changerecord->adminid),
                    ];
                }
            }
        }
        return $changes;
    }

    /**
     * Find the new value for a given column and row
     * @param array $changerecords
     * @param int $rowid
     * @param string $column
     * @param mixed $value
     * @return mixed
     */
    public static function find_new_value($changerecords, $rowid, $column, $value) {
        global $USER;
        foreach ($changerecords as $changerecord) {
            if ($changerecord->get('pid') == $rowid && $changerecord->get('field') == $column
                && $changerecord->get('usermodified') == $USER->id) {
                return $changerecord->get('newvalue');
            }
        }
        return $value;
    }

    /**
     * Get the rfc data for a given course
     * @param int $courseid
     * @param bool $showrfc
     * @return array $data
     */
    public static function get_rfc_data(int $courseid, bool $showrfc = false): array {
        global $USER;
        $changerecords = [];
        if ($showrfc) {
            $changerecords = sprogramme_change::get_all_records_for_course($courseid);
        }
        $data = [];
        foreach ($changerecords as $changerecord) {
            $userid = $changerecord->get('usermodified');
            $cansumit = has_capability('customfield/sprogramme:edit', context_course::instance($courseid)) && $userid == $USER->id;
            $canaccept = has_capability('customfield/sprogramme:editall', context_course::instance($courseid));
            $issubmitted = $changerecord->get('action') == sprogramme_change::RFC_SUBMITTED;
            $key = $issubmitted ? $userid : ($userid . '_requested');
            $data[$key]['issubmitted'] = $issubmitted;
            $data[$key]['timemodified'] = $changerecord->get('timemodified');
            $data[$key]['userinfo'] = self::get_user_info($key);
            $data[$key]['canaccept'] = $canaccept;
            $data[$key]['cansubmit'] = $cansumit && !$issubmitted && !$canaccept;
            $data[$key]['cancancel'] = $cansumit && $issubmitted && !$canaccept;
        }
        return array_values($data);
    }

    /**
     * Check if a course has submitted rfcs
     * @param int $courseid
     * @return bool
     */
    public static function has_submitted_rfcs(int $courseid): bool {
        $changerecords = sprogramme_change::get_all_records_for_course($courseid);
        foreach ($changerecords as $changerecord) {
            if ($changerecord->get('action') == sprogramme_change::RFC_SUBMITTED) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a user can add a new rfc for a course
     * (there shoubld be no submitted rfcs yet) and the user has the capability to edit
     * @param int $courseid
     * @return bool
     */
    public static function can_add_rfc(int $courseid): bool {
        global $USER;
        $coursecontext = context_course::instance($courseid);
        if (!has_capability('customfield/sprogramme:edit', $coursecontext)) {
            return false;
        }
        if (self::has_submitted_rfcs($courseid)) {
            return false;
        }
        return true;
    }

    /**
     * Get the number of submitted rfcs for a course
     * @param int $courseid
     * @return int
     */
    public static function get_numrfcs(int $courseid): int {
        $changerecords = sprogramme_change::get_all_records_for_course($courseid);
        $data = [];
        foreach ($changerecords as $changerecord) {
            if ($changerecord->get('action') == sprogramme_change::RFC_SUBMITTED) {
                $key = $changerecord->get('usermodified');
                $data[$key] = true;
            }
        }
        return count($data);
    }

    /**
     * Check if there is any data in the programme for a given course
     * @param int $courseid
     * @return bool
     */
    public static function has_data(int $courseid): bool {
        $cache = cache::make('customfield_sprogramme', 'programmedata');
        if ($data = $cache->get($courseid)) {
            if (is_array($data) && !empty($data) && isset($data[0]) && isset($data[0]['rows']) && !empty($data[0]['rows'])) {
                return true;
            } else {
                return false;
            }
        }
        $modules = sprogramme_module::get_all_records_for_course($courseid);
        if (empty($modules)) {
            return false;
        }
        foreach ($modules as $module) {
            $records = sprogramme::get_all_records_for_module($module->get('id'));
            if (!empty($records)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the data for a given course
     * @param int $courseid
     * @param bool $showrfc
     * @param bool $showcompet
     * @param bool $showdisc
     * @return array $data
     */
    public static function get_data(int $courseid, bool $showrfc = false, bool $showcompet = false, bool $showdisc = false): array {
        $cache = cache::make('customfield_sprogramme', 'programmedata');
        if ($data = $cache->get($courseid)) {
            // Cache turned off, the capabilities ar not set per user when using the cache.
            // return $data;
        }
        $modules = sprogramme_module::get_all_records_for_course($courseid);
        $canaddrfc = self::can_add_rfc($courseid);
        $changerecords = $showrfc ? sprogramme_change::get_all_records_for_course($courseid, $canaddrfc) : [];
        $columns = self::get_column_structure($courseid);
        $data = [];
        $sum = [];
        $newsum = [];
        // Set the sum to 0 for each column
        foreach ($columns as $column) {
            if (isset($column['sum'])) {
                $sum[$column['column']] = 0;
                $newsum[$column['column']] = 0;
            }
        }
        foreach ($modules as $module) {
            $records = sprogramme::get_all_records_for_module($module->get('id'));
            $modulerows = [];
            foreach ($records as $record) {
                $cells = [];
                foreach ($columns as $key => $column) {
                    $value = $record->get($column['column']);

                    $changes = self::find_change_record($changerecords, $record->get('id'), $column['column']);
                    $newvalue = self::find_new_value($changerecords, $record->get('id'), $column['column'], $value);
                    if ($column['type'] == PARAM_FLOAT || $column['type'] == PARAM_INT) {
                        $sum[$column['column']] += $value;
                        $newsum[$column['column']] += $newvalue;
                    }
                    $cells[] = [
                        'column' => $column['column'],
                        'value' => $newvalue,
                        'type' => $column['type'],
                        'visible' => $column['visible'],
                        'group' => $column['group'],
                        'changes' => $changes,
                    ];
                }

                $disciplinedata = [];
                if ($showdisc) {
                    $disciplines = sprogramme_disc::get_all_records_for_programme($record->get('id'));
                    foreach ($disciplines as $discipline) {
                        $disciplinedata[] = [
                            'id' => $discipline->get('did'),
                            'name' => $discipline->get('discipline'),
                            'percentage' => $discipline->get('percentage'),
                        ];
                    }
                }

                $competencydata = [];
                if ($showcompet) {
                    $competencies = sprogramme_comp::get_all_records_for_programme($record->get('id'));
                    foreach ($competencies as $competency) {
                        $competencydata[] = [
                            'id' => $competency->get('cid'),
                            'name' => $competency->get('competency'),
                            'percentage' => $competency->get('percentage'),
                        ];
                    }
                }

                $rowchanges = self::find_change_record($changerecords, $record->get('id'), 'row');
                if ($rowchanges) {
                    $cells[2]['changes'] = $rowchanges;
                }
                $modulerows[] = [
                    'id' => $record->get('id'),
                    'sortorder' => $record->get('sortorder'),
                    'cells' => $cells,
                    'disciplines' => $disciplinedata,
                    'competencies' => $competencydata,
                    'rowchanges' => $rowchanges ? true : false,
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
        if (isset($data[0])) {
            $data[0]['columns'] = array_map(function($column) use ($sum, $newsum) {
                $currentsum = 0;
                if (isset($sum[$column['column']])) {
                    $currentsum = $sum[$column['column']];
                    $column['sum'] = $currentsum ;
                    $column['hassum'] = true;
                }
                if (isset($newsum[$column['column']])) {
                    $column['newsum'] = $newsum[$column['column']];
                    $column['hasnewsum'] = $newsum[$column['column']] !== $currentsum ;
                }
                return $column;
            }, $data[0]['columns']);
        }

        $cache->set($courseid, $data);
        return $data;
    }

    /**
     * Get the column totals for a given course
     * @param int $courseid
     * @return array $totals
     */
    public static function get_column_totals(int $courseid): array {
        $cache = cache::make('customfield_sprogramme', 'columntotals');
        if ($data = $cache->get($courseid)) {
            return $data;
        }
        $columns = self::get_column_structure($courseid);
        $totals = [];
        foreach ($columns as $column) {
            if (isset($column['sum'])) {
                $totals[$column['column']] = [
                    'label' => $column['label'],
                    'sum' => 0,
                ];
            }
        }
        $data = self::get_data($courseid);
        if (isset($data[0]) && isset($data[0]['columns'])) {
            foreach ($data[0]['columns'] as $column) {
                if (isset($totals[$column['column']])) {
                    $totals[$column['column']]['sum'] = $column['sum'];
                }
            }
        }
        $cache->set($courseid, $totals);
        return $totals;
    }

    /**
     * Set the data.
     * @param int $courseid
     * @param array $data
     */
    public static function set_records(int $courseid, array $data): string {
        $result = '';
        $context = context_course::instance($courseid);
        $editall = has_capability('customfield/sprogramme:editall', $context);
        if ($editall) {
            self::delete_flagged_records($courseid, $data);
        }
        foreach ($data as $module) {
            if ($module['deleted'] == true) {
                continue; // Skip deleted modules.
            }
            $moduleid = $module['id'];
            $rows = $module['rows'];
            $mod = sprogramme_module::get_record(['id' => $moduleid]);
            if (!$mod) {
                $mod = new sprogramme_module();
                $mod->set('courseid', $courseid);
            }
            $mod->set('name', $module['name']);
            $mod->set('sortorder', $module['sortorder']);
            $mod->save();
            $moduleid = $mod->get('id');
            $records = sprogramme::get_all_records_for_module($moduleid);
            foreach ($rows as $row) {
                if ($row['deleted'] == true) {
                    continue; // Skip deleted rows.
                }
                $updated = false;
                foreach ($records as $record) {
                    if ($record->get('id') == $row['id']) {
                        $record->set('sortorder', $row['sortorder']);
                        $record->save();
                        $update = self::update_record($record, $row, $courseid);
                        if ($update != '') {
                            $result = $update;
                        }
                        $updated = true;
                    }
                }
                if (!$updated) {
                    $record = new sprogramme();
                    $record->set('uc', $courseid);
                    $record->set('courseid', $courseid);
                    $record->set('moduleid', $moduleid);
                    $record->set('sortorder', $row['sortorder']);
                    foreach ($row['cells'] as $cell) {
                        $record->set($cell['column'], null);
                    }
                    $record->save();
                    $row['id'] = $record->get('id');
                    $update = self::update_record($record, $row, $courseid);
                    if ($update != '') {
                        $result = $update;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Delete records
     * Deletes the modules and rows that have the deleted flag set to true.
     * @param int $courseid
     * @param array $data
     */
    public static function delete_flagged_records(int $courseid, array $data) {
        foreach ($data as $module) {
            $moduleid = $module['id'];
            // Delete the module.
            if ($module['deleted'] == true) {
                $mod = sprogramme_module::get_record(['id' => $moduleid]);
                if ($mod) {
                    $mod->delete();
                    $records = sprogramme::get_all_records_for_module($moduleid);
                    foreach ($records as $record) {
                        $record->delete();
                    }
                    continue;
                }
            }
            $rows = $module['rows'];
            foreach ($rows as $row) {
                // Delete the row.
                if ($row['deleted'] == true) {
                    $record = sprogramme::get_record(['id' => $row['id']]);
                    if ($record) {
                        $record->delete();
                    }
                }
            }
        }
    }

    /**
     * Update a record
     * @param sprogramme $record
     * @param array $row
     * @param int $courseid
     * @return string $result
     */
    private static function update_record(sprogramme $record, array $row, int $courseid): string {
        $context = context_course::instance($courseid);
        $editall = has_capability('customfield/sprogramme:editall', $context);
        $columns = self::get_column_structure($courseid);
        $changes = false;
        $result = '';
        foreach ($columns as $column) {
            if (!isset($row['cells'])) {
                continue;
            }
            $field = $column['column'];
            $canedit = $column['canedit'] || $editall;
            foreach ($row['cells'] as $cell) {
                if ($cell['column'] == $field) {
                    $currentvalue = $record->get($field);
                    if ($cell['value'] == $currentvalue) {
                        continue;
                    }
                    // Filter the $columns array, find columns fields with the same group, then see if a value is set
                    // for this fiel in $record. If so, then set the value to null.
                    $group = $column['group'];
                    if ($group != '') {
                        foreach ($columns as $col) {
                            if ($col['group'] == $group && $col['column'] != $field) {
                                $value = $record->get($col['column']);
                                if (!$canedit && $value) {
                                    $result = self::record_change_request($courseid, $row['id'], $col['column'],
                                        $column['group'], $value, null);
                                } else if ($value) {
                                    $record->set($col['column'], null);
                                }
                            }
                        }
                    }

                    $changes = true;
                    // Check if the user has permission to edit this field
                    if (!$canedit) {
                        $result = self::record_change_request($courseid, $row['id'], $field, $column['group'],
                            $record->get($field), $cell['value']);
                    } else if ($cell['type'] == PARAM_INT) {
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
        if ($changes) {
            $record->save();
            // Clear the cache for this course.
            self::purge_cache($courseid);
        }
        self::set_disciplines($record, $row);
        self::set_competencies($record, $row);
        return $result;
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
        if (!$module) {
            return false;
        }
        if ($module->get('courseid') == $courseid) {
            // Delete all rows in this module.
            $records = sprogramme::get_all_records_for_module($moduleid);
            foreach ($records as $record) {
                self::delete_row($courseid, $record->get('id'));
            }
            $recordsleft = sprogramme::get_all_records_for_module($moduleid);
            if (empty($recordsleft)) {
                // Delete the module if there are no rows left.
                $module->delete();
            }
            self::purge_cache($courseid);
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
        self::update_sort_order('row', $courseid, $moduleid, $record->get('id'), $prevrowid);
        self::purge_cache($courseid);
        return $record->get('id');
    }

    /**
     * Delete a row
     * @param int $courseid
     * @param int $rowid
     * @return bool
     */
    public static function delete_row($courseid, $rowid): bool {
        $context = context_course::instance($courseid);
        $editall = has_capability('customfield/sprogramme:editall', $context);
        $record = sprogramme::get_record(['id' => $rowid]);
        if ($record->get('courseid') == $courseid) {
            if ($editall) {
                $disciplines = sprogramme_disc::get_all_records_for_programme($rowid);
                foreach ($disciplines as $discipline) {
                    $discipline->delete();
                }
                $competencies = sprogramme_comp::get_all_records_for_programme($rowid);
                foreach ($competencies as $competency) {
                    $competency->delete();
                }
                $record->delete();
                self::purge_cache($courseid);
                return true;
            } else {
                $result = self::record_change_request($courseid, $rowid, 'row', '', '', -1);
                self::purge_cache($courseid);
                if ($result == 'rfclocked') {
                    return false;
                }
                if ($result == 'newrfc') {
                    return true;
                }
            }

        }
        return false;
    }

    /**
     * Update the sort order
     * @param string $type
     * @param int $courseid
     * @param int $moduleid
     * @param int $id
     * @param int $previd
     */
    public static function update_sort_order($type, $courseid, $moduleid, $id, $previd): void {
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
            self::purge_cache($courseid);
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
        foreach ($data as $module) {
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

    /**
     * Record a change request
     * @param int $courseid
     * @param int $rowid
     * @param string $field
     * @param string $group
     * @param mixed $oldvalue
     * @param mixed $newvalue
     * @return string $result
     */
    public static function record_change_request(int $courseid, int $rowid, string $field, string $group,
        $oldvalue, $newvalue): string {
        global $USER;
        $submitted = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $USER->id,
            'action' => sprogramme_change::RFC_SUBMITTED]);
        if (count($submitted) > 0) {
            return 'rfclocked';
        }
        $record = sprogramme_change::get_record(['pid' => $rowid, 'courseid' => $courseid, 'field' => $field,
            'usermodified' => $USER->id, 'action' => sprogramme_change::RFC_REQUESTED]);
        if (!$record) {
            $record = new sprogramme_change();
            $record->set('courseid', $courseid);
            $record->set('pid', $rowid);
            $record->set('field', $field);
        }
        $record->set('newrowid', 0);
        $record->set('action', sprogramme_change::RFC_REQUESTED);
        $record->set('oldvalue', $oldvalue ? $oldvalue : 0);
        $record->set('newvalue', $newvalue ? $newvalue : 0);
        $record->set('adminid', $USER->id);
        $record->set('snapshot', json_encode(self::get_data($courseid)));
        $record->save();

        return 'newrfc';
    }

    /**
     * Accept a change request by a user
     * @param int $courseid
     * @param int $userid
     * @return bool true if accepted
     */
    public static function accept_rfc(int $courseid, int $userid): bool {
        $result = false;
        $records = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $userid,
            'action' => sprogramme_change::RFC_SUBMITTED]);
        foreach ($records as $record) {
            $row = sprogramme::get_record(['id' => $record->get('pid')]);
            if (!$row) {
                continue;
            }
            if ($record->get('field') == 'row') {
                $row->delete();
            } else {
                $row->set($record->get('field'), $record->get('newvalue'));
                $row->save();
                $record->set('action', sprogramme_change::RFC_ACCEPTED);
                $record->save();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Reject a change request by a user
     * @param int $courseid
     * @param int $userid
     */
    public static function reject_rfc(int $courseid, int $userid): bool {
        $result = false;
        $records = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $userid,
            'action' => sprogramme_change::RFC_SUBMITTED]);
        foreach ($records as $record) {
            $record->set('action', sprogramme_change::RFC_REJECTED);
            $result = true;
            $record->save();
        }
        return $result;
    }

    /**
     * Submit a change request
     * @param int $courseid
     * @param int $userid
     * @return bool true if submitted
     */
    public static function submit_rfc(int $courseid, int $userid): bool {
        $result = false;
        $records = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $userid]);
        foreach ($records as $record) {
            $record->set('action', sprogramme_change::RFC_SUBMITTED);
            $record->save();
            $result = true;
        }
        notifications::setnotification('rfc', $userid, $courseid);
        return $result;
    }

    /**
     * Cancel a change request
     * @param int $courseid
     * @param int $userid
     * @return bool true if cancelled
     */
    public static function cancel_rfc(int $courseid, int $userid): bool {
        $result = false;
        $records = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $userid,
            'action' => sprogramme_change::RFC_SUBMITTED]);
        foreach ($records as $record) {
            $record->set('action', sprogramme_change::RFC_REQUESTED);
            $record->save();
            $result = true;
        }
        return $result;
    }

    /**
     * Remove a change request
     * @param int $courseid
     * @param int $userid
     * @return bool true if removed
     */
    public static function remove_rfc(int $courseid, int $userid): bool {
        $result = false;
        $records = sprogramme_change::get_records(['courseid' => $courseid, 'usermodified' => $userid,
            'action' => sprogramme_change::RFC_REQUESTED]);
        foreach ($records as $record) {
            $record->delete();
            $result = true;
        }
        return $result;
    }

    /**
     * Get the numeric columns
     * @return array $columns
     */
    public static function get_numeric_columns(): array {
        $columns = [];
        $table = self::get_table_structure();
        foreach ($table as $column) {
            if (isset($column['sum'])) {
                $columns[] = $column;
            }
        }
        return $columns;
    }

    /**
     * Get the sums of the numerice column values for a given courseid
     * @param int $courseid
     * @return array $columns
     */
    public static function get_sums(int $courseid): array {
        $numericcomlumns = self::get_numeric_columns();
        $data = self::get_data($courseid);
        // Sums are found in the first module.
        $columns = [];
        if (isset($data[0])) {
            $columns = $data[0]['columns'];
            // Filter out the numeric columns.
            $columns = array_filter($columns, function($column) use ($numericcomlumns) {
                foreach ($numericcomlumns as $numericcolumn) {
                    if ($column['column'] == $numericcolumn['column']) {
                        return true;
                    }
                }
                return false;
            });
        }
        return $columns;
    }

    /**
     * Get the programme history for a given rfc record.
     * @param int $courseid
     * @param int $adminid
     * @return array $history
     */
    public static function get_programme_history(int $courseid, int $adminid): array {
        $rfcrecords = sprogramme_change::get_course_rfcs($courseid, sprogramme_change::RFC_ACCEPTED, $adminid);
        if (!$rfcrecords) {
            return [];
        }
        $snapshot = $rfcrecords[0]->snapshot;
        if (!$snapshot) {
            return [];
        }
        $modules = json_decode($snapshot, true);
        if (!$modules) {
            return [];
        }
        // Add the $rfcrecords changes data to the data, the $modules is an array of modules, each module has rows,
        // each row has cells, and each cell has a value.
        foreach ($modules as &$module) {
            foreach ($module['rows'] as &$row) {
                $rowchanges = self::find_change_record($rfcrecords, $row['id'], 'row');
                if ($rowchanges) {
                    $row['changes'] = $rowchanges;
                }
                foreach ($row['cells'] as &$cell) {
                    $changes = self::find_change_record($rfcrecords, $row['id'], $cell['column']);
                    if ($changes) {
                        $cell['changes'] = $changes;
                    }
                }
            }
        }
        $rfcdata = [];
        foreach ($rfcrecords as $record) {
            $rfcdata[] = [
                'action' => $record->action,
                'userinfo' => self::get_user_info($record->adminid),
                'timecreated' => $record->timecreated,
                'timemodified' => $record->timemodified,
            ];
        }
        return [
            'modules' => $modules,
            'rfcs' => $rfcdata,
        ];
    }

    /**
     * Purge the cache for a given course
     * @param int $courseid
     */
    public static function purge_cache(int $courseid): void {
        $cache = cache::make('customfield_sprogramme', 'programmedata');
        $cache->delete($courseid);
        $cache = cache::make('customfield_sprogramme', 'columntotals');
        $cache->delete($courseid);
    }
}
