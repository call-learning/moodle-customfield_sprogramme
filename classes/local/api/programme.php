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
use customfield_sprogramme\local\persistent\sprogramme_rfc;
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
                'column' => 'dd_rse',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => true,
                'label' => 'DD / RSE',
                'help' => get_string('dd_rse_help', 'customfield_sprogramme'),
                'columnid' => 7,
                'length' => 20,
                'field' => 'select',
                'sample_value' => '',
                'options' => [
                    [
                        'name' => 'à remplir',
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
                'help' => get_string('intitule_seance_help', 'customfield_sprogramme'),
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
                'help' => get_string('cm_help', 'customfield_sprogramme'),
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
                'help' => get_string('td_help', 'customfield_sprogramme'),
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
                'help' => get_string('tp_help', 'customfield_sprogramme'),
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
                'help' => get_string('tpa_help', 'customfield_sprogramme'),
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
                'help' => get_string('tc_help', 'customfield_sprogramme'),
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
                'help' => get_string('aas_help', 'customfield_sprogramme'),
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
                'help' => get_string('fmp_help', 'customfield_sprogramme'),
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
                'help' => get_string('perso_av_help', 'customfield_sprogramme'),
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
                'help' => get_string('perso_ap_help', 'customfield_sprogramme'),
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
                'help' => get_string('consignes_help', 'customfield_sprogramme'),
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
                'help' => get_string('supports_help', 'customfield_sprogramme'),
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
     * Get the data for a given course
     * @param int $courseid
     * @param bool $showrfc
     * @param bool $showcompet
     * @param bool $showdisc
     * @return array $data
     */
    public static function get_data(int $courseid, bool $showrfc = false): array {
        if ($showrfc && self::has_submitted_rfc($courseid)) {
            // Note to self: need to ensure there is only ever one submitted RFC per course.
            $rfc = sprogramme_rfc::get_rfc($courseid);
            if ($rfc) {
                $data = $rfc->get('snapshot');
                $data = json_decode($data, true);
                return $data;
            }
        }

        $modules = sprogramme_module::get_all_records_for_course($courseid);
        $columns = self::get_column_structure($courseid);
        $data = [];
        foreach ($modules as $module) {
            $records = sprogramme::get_all_records_for_module($module->get('id'));
            $modulerows = [];
            foreach ($records as $record) {
                $cells = [];
                foreach ($columns as $key => $column) {
                    $value = $record->get($column['column']);
                    $cells[] = [
                        'column' => $column['column'],
                        'value' => $value,
                        'type' => $column['type'],
                        'visible' => $column['visible'],
                        'group' => $column['group'],
                        'oldvalue' => $value, // This should be the original value before any changes.
                    ];
                }

                $disciplinedata = [];
                $disciplines = sprogramme_disc::get_all_records_for_programme($record->get('id'));
                foreach ($disciplines as $discipline) {
                    $disciplinedata[] = [
                        'id' => $discipline->get('did'),
                        'name' => $discipline->get('discipline'),
                        'percentage' => $discipline->get('percentage'),
                    ];
                }

                $competencydata = [];
                $competencies = sprogramme_comp::get_all_records_for_programme($record->get('id'));
                foreach ($competencies as $competency) {
                    $competencydata[] = [
                        'id' => $competency->get('cid'),
                        'name' => $competency->get('competency'),
                        'percentage' => $competency->get('percentage'),
                    ];
                }

                $rowchanges = [];
                //$rowchanges = self::find_change_record($changerecords, $record->get('id'), 'row');
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
            ];
        }

        $cache = cache::make('customfield_sprogramme', 'programmedata');
        $cache->set($courseid, $data);

        return $data;
    }

    /**
     * Set the data.
     * @param int $courseid
     * @param array $data
     */
    public static function set_data(int $courseid, array $data): string {
        if (!self::can_edit($courseid)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'edit programme');
        }
        if (self::is_rfc_required($courseid) && self::has_protected_data_changes($data)) {
            if (!self::can_add_rfc($courseid)) {
                throw new \moodle_exception('nopermissions', 'error', '', 'add rfc');
            }
            self::create_rfc($courseid, $data);
            return 'newrfc';
        }
        self::delete_flagged_records($courseid, $data);
        foreach ($data as $module) {
            if ($module['deleted'] == true) {
                continue; // Skip deleted modules.
            }
            $moduleid = $module['moduleid'];
            $rows = $module['rows'];
            $mod = sprogramme_module::get_record(['id' => $moduleid]);
            if (!$mod) {
                $mod = new sprogramme_module();
                $mod->set('courseid', $courseid);
            }
            $mod->set('name', $module['modulename']);
            $mod->set('sortorder', $module['modulesortorder']);
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
                        self::update_record($record, $row, $courseid);
                        $updated = true;
                    }
                }
                if (!$updated) {
                    if (self::is_rfc_required($courseid)) {
                        self::rfc_add_row($courseid, $moduleid, $row['sortorder'], $row['cells']);
                    } else {
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
                        self::update_record($record, $row, $courseid);
                    }
                }
            }
        }
        self::purge_cache($courseid);
        return true;
    }

    /**
     * Validate the data for a given course
     * @param int $courseid
     * @param array $data
     * @return array $errors
     */
    public static function validate_data(int $courseid, array $data): array {
        $errors = [];
        $columns = self::get_column_structure($courseid);
        foreach ($data as $module) {
            if ($module['deleted'] == true) {
                continue; // Skip deleted modules.
            }
            foreach ($module['rows'] as $row) {
                if ($row['deleted'] == true) {
                    continue; // Skip deleted rows.
                }
                $rowchecked = false;
                foreach ($row['cells'] as $cell) {
                    $column = array_filter($columns, function($col) use ($cell) {
                        return $col['column'] === $cell['column'];
                    });
                    if (empty($column)) {
                        continue; // Skip if column not found.
                    }
                    $column = reset($column);
                    if ($cell['value'] === '' && !$column['canedit']) {
                        continue; // Skip empty values for non-editable columns.
                    }
                    if (!self::validate_cell_value($cell['value'], $column)) {
                        $errors[] = [
                            'module' => $module['modulename'],
                            'row' => $row['sortorder'],
                            'column' => $column['label'],
                            'error' => get_string('invalidvalue', 'customfield_sprogramme', ['value' => $cell['value']]),
                        ];
                    }
                }
            }
        }
        return $errors;
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
        $canedit = self::can_edit($courseid);
        $canaddrfc = self::can_add_rfc($courseid);
        $editall = $canaddrfc && has_capability('customfield/sprogramme:editall', context_course::instance($courseid));
        $table = array_map(function($column) use ($canedit, $canaddrfc, $editall) {
            if ($column['canedit'] == false) {
                $column['canaddrfc'] = $canaddrfc;
                $column['canedit'] = $canedit;
                $column['protected'] = $editall ? false : true; // If the user can edit all, the column is not protected.
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
     * Find the new value for a given column and row
     * @param array $changerecords
     * @param int $rowid
     * @param string $column
     * @param mixed $value
     * @return mixed
     */
    public static function find_new_value($changerecords, $rowid, $column, $value) {
        foreach ($changerecords as $changerecord) {
            foreach ($changerecord->changes as $change) {
                if ($change->pid == $rowid && $change->field == $column) {
                    // If the change is for the current column and row, return the new value.
                    if ($change->newvalue !== null) {
                        return $change->newvalue;
                    }
                }
            }
        }
        return $value; // Return the original value if no change is found.
    }

    /**
     * Get the rfc data for a given course
     * @param int $courseid
     * @return array $data
     */
    public static function get_rfc_data(int $courseid): array {
        global $USER;
        $changerecord = sprogramme_rfc::get_rfc($courseid);
        if (!$changerecord) {
            return []; // No RFC found for the course.
        }

        $data = [];

        $userid = $changerecord->get('adminid');
        $cansumit = has_capability('customfield/sprogramme:edit', context_course::instance($courseid)) && $userid == $USER->id;
        $canaccept = has_capability('customfield/sprogramme:editall', context_course::instance($courseid));
        $issubmitted = $changerecord->get('type') == sprogramme_rfc::RFC_SUBMITTED;

        $data['issubmitted'] = $issubmitted;
        $data['timemodified'] = $changerecord->get('timemodified');
        $data['userinfo'] = self::get_user_info($userid);
        $data['canaccept'] = $canaccept;
        $data['cansubmit'] = $cansumit && !$issubmitted && !$canaccept;
        $data['cancancel'] = $cansumit && $issubmitted && !$canaccept;
        return $data;
    }

    /**
     * Check if a course has submitted rfcs
     * @param int $courseid
     * @return bool
     */
    public static function has_submitted_rfc(int $courseid): bool {
        $changerecord = sprogramme_rfc::get_rfc($courseid);
        if ($changerecord) {
            return true; // If there is a change record for the course, it means there are submitted rfcs.
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
        if (self::has_submitted_rfc($courseid)) {
            return false;
        }
        return true;
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
     * Get the column totals for a given course
     * @param array $modules
     * @param array $columns
     * @return array $columns
     */
    public static function get_column_totals(array $modules, array $columns): array {
        $totals = [];
        foreach ($modules as $module) {
            foreach ($module['rows'] as $row) {
                foreach ($row['cells'] as $cell) {
                    if (isset($cell['value']) && ($cell['type'] == PARAM_FLOAT || $cell['type'] == PARAM_INT)) {
                        if (!isset($totals[$cell['column']])) {
                            $totals[$cell['column']] = 0;
                        }

                        $totals[$cell['column']] += $cell['type'] == PARAM_FLOAT ? (float)$cell['value'] : (int)$cell['value'];
                    }
                }
            }
        }
        // Add the totals to the columns.
        foreach ($columns as &$column) {
            if (isset($totals[$column['column']])) {
                $column['sum'] = $totals[$column['column']];
            } else {
                $column['sum'] = 0;
            }
        }
        return $columns;
    }

    /**
     * Check to see if a user can edit the programme
     * @param int $courseid
     * @return bool
     * */
    public static function can_edit(int $courseid): bool {
        $context = context_course::instance($courseid);
        if (self::has_submitted_rfc($courseid)) {
            // If there is a submitted RFC, the user cannot edit the programme.
            return false;
        }
        if (has_capability('customfield/sprogramme:editall', $context)) {
            return true; // If the user has the capability to edit all, they can edit the programme.
        }
        if (has_capability('customfield/sprogramme:edit', $context)) {
            return true; // If the user has the capability to edit, they can edit the programme.
        }
        return false; // Otherwise, the user cannot edit the programme.
    }

    /**
     * Delete records
     * Deletes the modules and rows that have the deleted flag set to true.
     * @param int $courseid
     * @param array $data
     */
    public static function delete_flagged_records(int $courseid, array $data) {
        foreach ($data as $module) {
            $moduleid = $module['moduleid'];
            // Delete the module.
            if ($module['deleted'] == true) {
                self::delete_module($courseid, $moduleid);
                continue; // Skip deleted modules.
            }
            $rows = $module['rows'];
            foreach ($rows as $row) {
                // Delete the row.
                if ($row['deleted'] == true) {
                    self::delete_row($courseid, $row['id']);
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
    private static function update_record(sprogramme $record, array $row, int $courseid) {
        $columns = self::get_column_structure($courseid);
        $changes = false;

        foreach ($columns as $column) {
            if (!isset($row['cells'])) {
                continue;
            }
            $field = $column['column'];
            foreach ($row['cells'] as $cell) {
                if ($cell['column'] == $field) {
                    $currentvalue = $record->get($field);
                    if ($cell['value'] == $currentvalue) {
                        continue;
                    }
                    if ($cell['type'] == PARAM_INT) {
                        $record->set($field, (int)$cell['value']);
                    } else if ($cell['type'] == PARAM_FLOAT) {
                        $record->set($field, (float)$cell['value']);
                    } else {
                        $record->set($field, $cell['value']);
                    }

                    $changes = true;
                }
            }
        }
        if ($changes) {
            $record->save();
        }
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
            [
                ['column' => 'disciplines_1'], ['column' => '%_disciplines_1'],
                ['column' => 'disciplines_2'], ['column' => '%_disciplines_2'],
                ['column' => 'disciplines_3'], ['column' => '%_disciplines_3'],
                ['column' => 'competencies_1'], ['column' => '%_competencies_1'],
                ['column' => 'competencies_2'], ['column' => '%_competencies_2'],
                ['column' => 'competencies_3'], ['column' => '%_competencies_3'],
                ['column' => 'competencies_4'], ['column' => '%_competencies_4'],
            ]);
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
                for ($i = 0; $i < 3; $i++) {
                    if (isset($row['disciplines'][$i])) {
                        $discipline = $row['disciplines'][$i];
                        $cells[] = $discipline['name'];
                        $cells[] = $discipline['percentage'];
                    } else {
                        $cells[] = '';
                        $cells[] = '';
                    }
                }
                for ($i = 0; $i < 4; $i++) {
                    if (isset($row['competencies'][$i])) {
                        $competency = $row['competencies'][$i];
                        $cells[] = $competency['name'];
                        $cells[] = $competency['percentage'];
                    } else {
                        $cells[] = '';
                        $cells[] = '';
                    }
                }
                $csvexport->add_data(array_merge([$name], $cells));
            }
        }
        return $csvexport->print_csv_data(true);
    }

    /**
     * Check to see if a rfc is required for a given course
     * @param int $courseid
     * @return bool
     */
    public static function is_rfc_required(int $courseid): bool {
        $context = context_course::instance($courseid);
        if (has_capability('customfield/sprogramme:editall', $context)) {
            return false; // If the user has the capability to edit all, no rfc is required.
        }
        return true;
    }

    /**
     * Are there any data changes for columns that have canedit set to false?
     * @param array $data
     * @return bool
     */
    public static function has_protected_data_changes(array $data): bool {
        $columns = self::get_table_structure();
        foreach ($data as $module) {
            if ($module['deleted'] == true) {
                continue; // Skip deleted modules.
            }
            foreach ($module['rows'] as $row) {
                if ($row['deleted'] == true) {
                    continue; // Skip deleted rows.
                }
                foreach ($row['cells'] as $cell) {
                    $column = array_filter($columns, function($col) use ($cell) {
                        return $col['column'] === $cell['column'];
                    });
                    if (empty($column)) {
                        continue; // Skip if column not found.
                    }
                    $column = reset($column);
                    if ($column['canedit'] == false && $cell['value'] != $cell['oldvalue']) {
                        // If the column cannot be edited and the value is not empty, there are data changes.
                        return true;
                    }
                }
            }
        }
        return false; // No data changes found for columns that cannot be edited.
    }

    /**
     * Get the rfc for a given course and user
     *
     * @param int $courseid
     * @param int $userid
     * @param mixed $data
     * @return sprogramme_rfc
     */
    public static function create_rfc(int $courseid, mixed $data): sprogramme_rfc {
        global $USER;
        $rfc = sprogramme_rfc::get_record(
            [
                'courseid' => $courseid,
                'adminid' => $USER->id,
                'type' => sprogramme_rfc::RFC_REQUESTED
            ]);

        if (!$rfc) {
            $rfc = new sprogramme_rfc();
            $rfc->set('courseid', $courseid);
            $rfc->set('adminid', IntVal($USER->id));
            $rfc->set('snapshot', json_encode($data));
            $rfc->set('type', sprogramme_rfc::RFC_REQUESTED);
            $rfc->save();
        } else {
            // If the rfc already exists, update the snapshot.
            $rfc->set('snapshot', json_encode($data));
            $rfc->save();
        }
        return $rfc;
    }

    /**
     * Accept a change request by a user
     * @param int $courseid
     * @param int $userid
     * @return bool true if accepted
     */
    public static function accept_rfc(int $courseid, int $userid): bool {
        $result = false;
        $rfc = sprogramme_rfc::get_record(['courseid' => $courseid, 'adminid' => $userid,
            'type' => sprogramme_rfc::RFC_SUBMITTED]);
        if (!$rfc) {
            return false; // No submitted rfc found for the course and user.
        }
        $snapshot = $rfc->get('snapshot');
        if (!$snapshot) {
            return false; // No snapshot found in the rfc.
        }
        $data = json_decode($snapshot, true);
        if (!$data) {
            return false; // No data found in the snapshot.
        }
        // Set the data for the programme.
        $rfc->set('type', sprogramme_rfc::RFC_ACCEPTED);
        $rfc->save();
        $result = self::set_data($courseid, $data);
        if (!$result) {
            return false; // If setting the data failed, return false.
        }

        return true;
    }

    /**
     * Reject a change request by a user
     * @param int $courseid
     * @param int $userid
     */
    public static function reject_rfc(int $courseid, int $userid): bool {
        $result = false;
        $rfc = sprogramme_rfc::get_record(['courseid' => $courseid, 'adminid' => $userid,
            'type' => sprogramme_rfc::RFC_SUBMITTED]);
        if ($rfc) {
            $rfc->set('type', sprogramme_rfc::RFC_REJECTED);
            $rfc->save();
            $result = true;
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
        $record = sprogramme_rfc::get_record(
            [
                'courseid' => $courseid,
                'adminid' => $userid,
                'type' => sprogramme_rfc::RFC_REQUESTED
            ]);
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_SUBMITTED);
            $record->save();
            $result = true;
            notifications::setnotification('rfc', $userid, $courseid);
        } else {
            // If the record does not exist, we cannot submit it.
            return false;
        }
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
        $record = sprogramme_rfc::get_record(['courseid' => $courseid, 'adminid' => $userid,
            'type' => sprogramme_rfc::RFC_SUBMITTED]);
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_REQUESTED);
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
        $records = sprogramme_rfc::get_records(['courseid' => $courseid, 'usermodified' => $userid,
            'type' => sprogramme_rfc::RFC_REQUESTED]);
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
        $columnstructure = programme::get_column_structure($courseid);
        $columnstotals = programme::get_column_totals($data, $columnstructure);
        // Sums are found in the first module.
        $columns = [];

        // Filter out the numeric columns.
        $columns = array_filter($columnstotals, function($column) use ($numericcomlumns) {
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
     * Get the programme history for a given rfc record.
     * @param int $rfcid
     * @param int $courseid
     * @return array $history
     */
    public static function get_programme_history(int $rfcid, int $courseid): array {
        $rfcrecord = sprogramme_rfc::get_record(['id' => $rfcid, 'courseid' => $courseid]);
        if (!$rfcrecord) {
            return [];
        }
        $snapshot = $rfcrecord->get('snapshot');
        if (!$snapshot) {
            return [];
        }
        $modules = json_decode($snapshot, true);
        if (!$modules) {
            return [];
        }
        $rfcdata = [];
        $rfcdata[] = [
            'action' => $rfcrecord->get('type'),
            'userinfo' => self::get_user_info($rfcrecord->get('adminid')),
            'timecreated' => $rfcrecord->get('timecreated'),
            'timemodified' => $rfcrecord->get('timemodified'),
        ];
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
