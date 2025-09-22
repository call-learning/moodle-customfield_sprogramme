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

namespace customfield_sprogramme\local;

use cache_helper;
use context_system;
use core_cache\cache;
use csv_export_writer;
use customfield_sprogramme\local\helpers\programme_table_structure;
use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_comp;
use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_disc;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\persistent\sprogramme_module;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\utils;

/**
 * Class rfc_manager
 *
 * It is manager that will extend the custom data field functionality to manage the rfc data.
 *
 * Mainly we would love to have a datafield->get('programme') that returns the programme manager object but
 * this is prevented by the "final" declaration of the datafield class.
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme_manager {
    /** @var \context $context The context of the datafield. */
    private \context $context;

    /** @var int $courseid The course id. */
    private int $courseid;

    /**
     * Constructor
     *
     * @param int $datafieldid
     */
    public function __construct(
        /** @var int $datafieldid */
        private int $datafieldid,
    ) {
        global $DB;
        if (empty($this->datafieldid)) {
            throw new \coding_exception('Datafieldid or datacontroller must be provided to the programme manager.');
        }
        $this->context = utils::get_context_from_datafieldid($this->datafieldid) ?? context_system::instance();
        // We fetch the course id from the custom field instance directly.
        // This means if we use this field in another context than a course, this will fail.
        $this->courseid = $DB->get_field('customfield_data', 'instanceid', ['id' => $this->datafieldid], MUST_EXIST);
    }

    /**
     * Get the table structure for the custom field
     *
     * @return array $table
     */
    public static function get_table_structure(): array {
        return programme_table_structure::get();
    }

    /**
     * Get the numeric columns
     *
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
     * Set the data.
     *
     * @param array $data
     * @return bool true on success
     */
    public function set_data(array $data): bool {
        $this->delete_flagged_records($data);
        foreach ($data as $module) {
            $ismoduledeleted = $module['deleted'] ?? false;
            if ($ismoduledeleted) {
                continue; // Skip deleted modules.
            }
            $moduleid = $module['moduleid'] ?? null;
            $rows = $module['rows'];
            if ($moduleid <= 0) { // If we have negative id, it means it is a new module.
                // Create the module if it does not exist.
                $moduleid = $this->create_module($module['modulename'], $module['modulesortorder']);
            } else {
                $mod = sprogramme_module::get_record(['id' => $moduleid]);
                $mod->set('name', $module['modulename']);
                $mod->set('sortorder', $module['modulesortorder']);
                $mod->save();
                $moduleid = $mod->get('id');
            }
            foreach ($rows as $row) {
                $isrowdeleted = $row['deleted'] ?? false;
                $rowid = $row['id'] ?? null;
                if ($isrowdeleted) {
                    continue; // Skip deleted rows.
                }
                if ($rowid <= 0) { // If we have negative id, it means it is a new row.
                    // Create the row if it does not exist.
                    $rowid = $this->create_row($moduleid, abs($rowid) - 1);
                    $row['id'] = $rowid;
                }
                $record = sprogramme::get_record(['id' => $rowid]);
                $this->update_row($record, $row);
            }
        }
        $this->invalidate_cache();
        return true;
    }

    /**
     * Get the column structure for the custom field
     *
     * @return array $columns
     */
    public function get_column_structure(): array {
        $table = self::get_table_structure();
        $canedit = $this->can_edit();
        $rfc = new rfc_manager($this->datafieldid);
        $canaddrfc = $rfc->can_add();
        $editall =
            $canaddrfc && has_capability('customfield/sprogramme:editall', utils::get_context_from_datafieldid($this->datafieldid));

        $table = array_map(function($column) use ($canedit, $canaddrfc, $editall) {
            if (!$column['canedit']) {
                $column['canaddrfc'] = $canaddrfc;
                $column['canedit'] = $canedit;
                $column['protected'] = $editall ? false : true; // If the user can edit all, the column is not protected.
            }
            return $column;
        }, $table);
        return array_values($table);
    }

    /**
     * Check to see if a user can edit the programme
     *
     * @return bool
     * */
    public function can_edit(): bool {
        $rfc = new rfc_manager($this->datafieldid);
        if ($rfc->has_submitted()) {
            // If there is a submitted RFC, the user cannot edit the programme.
            return false;
        }
        if (has_capability('customfield/sprogramme:editall', $this->context)) {
            return true; // If the user has the capability to edit all, they can edit the programme.
        }
        if (has_capability('customfield/sprogramme:edit', $this->context)) {
            return true; // If the user has the capability to edit, they can edit the programme.
        }
        return false; // Otherwise, the user cannot edit the programme.
    }

    /**
     * Validate the data for a given course
     *
     * @param array $data
     * @return array $errors
     */
    public function validate_data(array $data): array {
        $errors = [];
        $columns = $this->get_column_structure();
        foreach ($data as $module) {
            $moduledeleted = $module['deleted'] ?? false;
            if ($moduledeleted) {
                continue; // Skip deleted modules.
            }
            foreach ($module['rows'] as $row) {
                $rowdeleted = $row['deleted'] ?? false;
                if ($rowdeleted) {
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
                    if (!$this->validate_cell_value($cell['value'], $column)) {
                        $errors[] = [
                            'module' => $module['modulename'],
                            'row' => $row['sortorder'],
                            'column' => $column['label'],
                            'error' => get_string(
                                'invalidvalue',
                                'customfield_sprogramme',
                                [
                                    'column' => $column['label'],
                                    'value' => $cell['value'],
                                ],
                            ),
                        ];
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Check if there are accepted or rejected rfcs for a course
     *
     * @return bool
     */
    public function has_history(): bool {
        return sprogramme_rfc::record_exists_select(
            "datafieldid = :datafieldid AND (type = :accepted OR type = :rejected)",
            [
                'datafieldid' => $this->datafieldid,
                'accepted' => sprogramme_rfc::RFC_ACCEPTED,
                'rejected' => sprogramme_rfc::RFC_REJECTED,
            ]
        );
    }

    /**
     * Check if there is any data in the programme for a given course
     * d
     *
     * @return bool
     */
    public function has_data(): bool {
        $cache = cache::make('customfield_sprogramme', 'programmedata');
        if ($data = $cache->get($this->datafieldid)) {
            if (is_array($data) && !empty($data) && isset($data[0]) && isset($data[0]['rows']) && !empty($data[0]['rows'])) {
                return true;
            } else {
                return false;
            }
        }
        $modules = sprogramme_module::get_all_records_for_datafieldid($this->datafieldid);
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
     * Delete a programme for a given course
     *
     */
    public function delete_programme(): void {
        $modules = sprogramme_module::get_all_records_for_datafieldid($this->datafieldid);
        foreach ($modules as $module) {
            $this->delete_module($module->get('id'));
        }
    }

    /**
     * Update the sort order
     *
     * @param string $type
     * @param int $moduleid
     * @param int $id
     * @param int $previd
     */
    public function update_sort_order($type, $moduleid, $id, $previd): void {
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
            $this->invalidate_cache();
        }
    }

    /**
     * Get the data in csv format
     *
     * @return string $csv
     */
    public function get_csv_data(): string {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');
        $data = $this->get_data();
        $csvexport = new csv_export_writer('comma', '"');
        $course = get_course($this->courseid);
        $filename = 'programme_' . $course->shortname . '_' . date('Ymd_His') . '.txt';
        $csvexport->set_filename($filename);
        $columns = $this->get_column_structure();
        // Add the module name to the first item of the columns.
        $columns = array_merge(
            [
                ['column' => 'module']
            ],
            $columns,
            [
                ['column' => 'disciplines1'], ['column' => '%_disciplines1'],
                ['column' => 'disciplines2'], ['column' => '%_disciplines2'],
                ['column' => 'disciplines3'], ['column' => '%_disciplines3'],
                ['column' => 'competencies1'], ['column' => '%_competencies1'],
                ['column' => 'competencies2'], ['column' => '%_competencies2'],
                ['column' => 'competencies3'], ['column' => '%_competencies3'],
                ['column' => 'competencies4'], ['column' => '%_competencies4'],
            ]
        );
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
                        $disc = sprogramme_disclist::get_record(['uniqueid' => $discipline['id']]);
                        $cells[] = $disc->get('name');
                        $cells[] = $discipline['percentage'];
                    } else {
                        $cells[] = '';
                        $cells[] = '';
                    }
                }
                for ($i = 0; $i < 4; $i++) {
                    if (isset($row['competencies'][$i])) {
                        $competency = $row['competencies'][$i];
                        $comp = sprogramme_complist::get_record(['uniqueid' => $competency['id']]);
                        $cells[] = $comp->get('name');
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
     * Get the data for a given course
     *
     * @return array $data
     */
    public function get_data(): array {
        $modules = sprogramme_module::get_all_records_for_datafieldid($this->datafieldid);
        $columns = self::get_column_structure();
        $data = [];
        foreach ($modules as $module) {
            $records = sprogramme::get_all_records_for_module($module->get('id'));
            $modulerows = [];
            foreach ($records as $record) {
                $cells = [];
                foreach ($columns as $key => $column) {
                    $value = $record->get($column['column']);
                    $cells[] = ['column' => $column['column'], 'value' => $value, 'type' => $column['type'],
                        'visible' => $column['visible'], 'group' => $column['group'], 'oldvalue' => $value,
                        // This should be the original value before any changes.
                    ];
                }

                $disciplinedata = [];
                $disciplines = sprogramme_disc::get_all_records_for_programme($record->get('id'));
                foreach ($disciplines as $discipline) {
                    $disciplinedata[] = [
                        'id' => $discipline->get('did'),
                        'name' => $discipline->get_name(),
                        'percentage' => $discipline->get('percentage'),
                    ];
                }

                $competencydata = [];
                $competencies = sprogramme_comp::get_all_records_for_programme($record->get('id'));
                foreach ($competencies as $competency) {
                    $competencydata[] = [
                        'id' => $competency->get('cid'),
                        'name' => $competency->get_name(),
                        'percentage' => $competency->get('percentage'),
                    ];
                }

                $rowchanges = [];
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
        $cache->set($this->datafieldid, $data);

        return $data;
    }

    /**
     * Are there any data changes for columns that have canedit set to false?
     *
     * @param array $data
     * @return bool
     */
    public function has_protected_data_changes(array $data): bool {
        $columns = self::get_table_structure();
        foreach ($data as $module) {
            $moduledeleted = $module['deleted'] ?? false;
            if ($moduledeleted) {
                continue; // Skip deleted modules.
            }
            foreach ($module['rows'] as $row) {
                $rowdeleted = $row['deleted'] ?? false;
                if ($rowdeleted) {
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
                    $oldvalue = $cell['oldvalue'] ?? '';
                    if ($column['canedit'] == false && $cell['value'] != $oldvalue) {
                        // If the column cannot be edited and the value is not empty, there are data changes.
                        return true;
                    }
                }
            }
        }
        return false; // No data changes found for columns that cannot be edited.
    }

    /**
     * Get the sums of the numerice column values for a given courseid
     *
     * @return array $columns
     */
    public function get_sums(): array {
        $numericcomlumns = self::get_numeric_columns();
        $data = self::get_data();
        $columnstructure = self::get_column_structure();
        $columnstotals = self::get_column_totals($data, $columnstructure);
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
     * Get the column totals for a given course
     *
     * @param array $modules
     * @param array $columns
     * @return array $columns
     */
    public function get_column_totals(array $modules, array $columns): array {
        $totals = [];
        foreach ($modules as $module) {
            foreach ($module['rows'] as $row) {
                foreach ($row['cells'] as $cell) {
                    if (isset($cell['value']) && ($cell['type'] == PARAM_FLOAT || $cell['type'] == PARAM_INT)) {
                        if (!isset($totals[$cell['column']])) {
                            $totals[$cell['column']] = 0;
                        }

                        $totals[$cell['column']] += $cell['type'] == PARAM_FLOAT ? (float) $cell['value'] : (int) $cell['value'];
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
     * Get the programme history for a given rfc record.
     *
     * @param int $rfcid
     * @return array $history
     */
    public function get_history(int $rfcid): array {
        $rfcrecord = sprogramme_rfc::get_record(['id' => $rfcid, 'datafieldid' => $this->datafieldid]);
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
            'userinfo' => utils::get_user_info($rfcrecord->get('adminid')),
            'timecreated' => $rfcrecord->get('timecreated'),
            'timemodified' => $rfcrecord->get('timemodified'),
        ];
        return ['modules' => $modules, 'rfcs' => $rfcdata];
    }

    /**
     * Delete records
     * Deletes the modules and rows that have the deleted flag set to true.
     *
     * @param array $data
     */
    private function delete_flagged_records(array $data) {
        foreach ($data as $module) {
            $moduleid = $module['moduleid'] ?? null;
            if (!$moduleid) {
                continue; // Skip if no module id as it means it has not yet been created.
            }
            // Delete the module.
            $moduledeleted = $module['deleted'] ?? false;
            if ($moduledeleted == true) {
                $this->delete_module($moduleid);
                continue; // Skip deleted modules.
            }
            $rows = $module['rows'];
            foreach ($rows as $row) {
                $rowid = $row['id'] ?? null;
                if (!$rowid) {
                    continue; // Skip if no row id as it means it has not yet been created.
                }
                // Delete the row.
                $rowdeleted = $row['deleted'] ?? false;
                if ($rowdeleted == true) {
                    $this->delete_row($row['id']);
                }
            }
        }
    }

    /**
     * Create a new module
     *
     * @param string $name
     * @param int $sortorder
     * @return int the module id
     */
    private function create_module(string $name, int $sortorder): int {
        $module = new sprogramme_module();
        $module->set('datafieldid', $this->datafieldid);
        $module->set('name', $name);
        $module->set('sortorder', $sortorder);
        $module->save();
        return $module->get('id');
    }

    /**
     * Update a row
     *
     * @param sprogramme $programme
     * @param array $row
     */
    private function update_row(sprogramme $programme, array $row) {
        $columns = $this->get_column_structure();
        $changes = false;

        foreach ($columns as $column) {
            if (!isset($row['cells'])) {
                continue;
            }
            $field = $column['column'];
            foreach ($row['cells'] as $cell) {
                if ($cell['column'] == $field) {
                    $currentvalue = $programme->get($field);
                    if ($cell['value'] == $currentvalue) {
                        continue;
                    }
                    if ($cell['type'] == PARAM_INT) {
                        $programme->set($field, (int) $cell['value']);
                    } else if ($cell['type'] == PARAM_FLOAT) {
                        $programme->set($field, (float) $cell['value']);
                    } else {
                        $programme->set($field, $cell['value']);
                    }

                    $changes = true;
                }
            }
        }
        if ($programme->get('sortorder') != ($row['sortorder'] ?? 0)) {
            $programme->set('sortorder', $row['sortorder'] ?? 0);
            $changes = true;
        }
        if ($changes) {
            $programme->save();
        }
        $this->set_programme_disciplines($programme, $row);
        $this->set_programme_competencies($programme, $row);
    }

    /**
     * Invalidate the cache for the programme data
     */
    private function invalidate_cache(): void {
        cache_helper::invalidate_by_event('customfield_sprogramme/changesinprogramme', [$this->datafieldid]);
    }

    /**
     * Delete a module
     *
     * @param int $moduleid
     * return bool
     */
    private function delete_module($moduleid): bool {
        $module = sprogramme_module::get_record(['id' => $moduleid]);
        if (!$module) {
            return false;
        }
        if ($module->get('datafieldid') == $this->datafieldid) {
            // Delete all rows in this module.
            $records = sprogramme::get_all_records_for_module($moduleid);
            foreach ($records as $record) {
                $this->delete_row($record->get('id'));
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
     * Delete a row
     *
     * @param int $rowid
     * @return bool
     */
    private function delete_row(int $rowid): bool {
        $record = sprogramme::get_record(['id' => $rowid, 'datafieldid' => $this->datafieldid]);
        if (!$record) {
            return false;
        }
        $disciplines = sprogramme_disc::get_all_records_for_programme($record->get('id'));
        foreach ($disciplines as $discipline) {
            $discipline->delete();
        }
        $competencies = sprogramme_comp::get_all_records_for_programme($record->get('id'));
        foreach ($competencies as $competency) {
            $competency->delete();
        }
        $record->delete();
        $this->invalidate_cache();
        return true;
    }

    /**
     * Set the disciplines
     *
     * @param sprogramme $record
     * @param array $row
     */
    private function set_programme_disciplines(sprogramme $record, array $row): void {
        if (!isset($row['disciplines']) || !is_array($row['disciplines'])) {
            return;
        }
        $disciplines = $row['disciplines'];

        $existing = [];
        if (!empty($row['id'])) {
            $existing = sprogramme_disc::get_all_records_for_programme($row['id']);
        }

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
     *
     * @param sprogramme $record
     * @param array $row
     */
    private function set_programme_competencies(sprogramme $record, array $row): void {
        if (!isset($row['competencies']) || !is_array($row['competencies'])) {
            return;
        }
        $competencies = $row['competencies'];
        $existing = [];
        if (!empty($row['id'])) {
            $existing = sprogramme_comp::get_all_records_for_programme($row['id']);
        }
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
     * Validate a cell value against its column definition
     *
     * @param mixed $value
     * @param array $column
     * @return bool
     */
    private function validate_cell_value(mixed $value, array $column): bool {
        if ($value === '' || $value === null) {
            return true; // Empty values are always valid.
        }
        $type = $column['type'];
        switch ($type) {
            case PARAM_INT:
            case PARAM_FLOAT:
            case PARAM_TEXT:
            case PARAM_ALPHANUM:
            case PARAM_ALPHANUMEXT:
            case PARAM_NOTAGS:
            case PARAM_TAGLIST:
            case PARAM_URL:
            case PARAM_FILE:
            case PARAM_EMAIL:
            case PARAM_LOCALURL:
            case PARAM_USERNAME:
            case PARAM_RAW:
                $cleanedparam = clean_param($value, $type);
                if ($cleanedparam != $value) {
                    return false; // There were some invalid characters or values.
                }
                break;
            case 'select':
                if (!isset($column['options']) || !is_array($column['options'])) {
                    return false; // No options to validate against.
                }
                return in_array($value, array_column($column['options'], 'name'));
        }
        return true;
    }

    /**
     * Create a new row
     *
     * @param int $moduleid
     * @param int $prevrowid
     * @return int $sortorder
     */
    private function create_row($moduleid, $prevrowid): int {
        if (!$moduleid) {
            $moduleid = $this->get_or_create_module('Module', 0);
        }
        $record = new sprogramme();
        $record->set('uc', $this->courseid);
        $record->set('moduleid', $moduleid);
        $record->set('datafieldid', $this->datafieldid);
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
        $this->update_sort_order('row', $moduleid, $record->get('id'), $prevrowid);
        $this->invalidate_cache();
        return $record->get('id');
    }

    /**
     * Get or create a module
     *
     * @param string $name
     * @param int $sortorder
     * @return int the module id
     */
    private function get_or_create_module(string $name, int $sortorder): int {
        $module = sprogramme_module::get_record(['datafieldid' => $this->datafieldid, 'name' => $name]);
        if ($module) {
            return $module->get('id');
        }
        return $this->create_module($name, $sortorder);
    }
}
