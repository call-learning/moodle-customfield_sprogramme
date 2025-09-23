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

namespace customfield_sprogramme\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\utils;

/**
 * Class get_programme_history
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_programme_history extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'rfcid' => new external_value(PARAM_INT, 'Rfc id', VALUE_DEFAULT, false),
            'datafieldid' => new external_value(PARAM_INT, 'Datafieldid id', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Get the programme history
     *
     * @param int $rfcid
     * @param int $datafieldid
     * @return array
     */
    public static function execute($rfcid, $datafieldid): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'rfcid' => $rfcid,
                'datafieldid' => $datafieldid,
            ]
        );
        $rfcid = $params['rfcid'];
        $datafieldid = $params['datafieldid'];

        // Validate course context.
        self::validate_context(utils::get_context_from_datafieldid($datafieldid));
        // Get the programme history.
        $programmemanager = new programme_manager($datafieldid);
        $history = $programmemanager->get_history($rfcid);
        $modules = $history['modules'];
        if (empty($modules)) {
            throw new \invalid_parameter_exception('No programme history found for this course.');
        }
        $rfcs = $history['rfcs'];
        if (empty($rfcs)) {
            throw new \invalid_parameter_exception('No programme history found for this course.');
        }
        $columns = $programmemanager->get_column_structure();
        $columnstotals = $programmemanager->get_column_totals($modules, $columns);

        return [
            'modules' => $modules,
            'columns' => $columnstotals,
            'rfcs' => $rfcs,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'modules' => new external_multiple_structure(
                new external_single_structure([
                    'moduleid' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                    'modulesortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                    'modulename' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                    'deleted' => new external_value(PARAM_BOOL, 'Deleted', VALUE_DEFAULT, false),
                    'rows' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                            'deleted' => new external_value(PARAM_BOOL, 'Deleted', VALUE_DEFAULT, false),
                            'cells' => new external_multiple_structure(
                                new external_single_structure([
                                    'column' => new external_value(PARAM_TEXT, 'Column id', VALUE_REQUIRED),
                                    'value' => new external_value(PARAM_TEXT, 'Value', VALUE_REQUIRED),
                                    'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
                                    'group' => new external_value(PARAM_TEXT, 'Group', VALUE_OPTIONAL),
                                    'oldvalue' => new external_value(PARAM_TEXT, 'Old value', VALUE_OPTIONAL),
                                ])
                            ),
                            'disciplines' => new external_multiple_structure(
                                new external_single_structure([
                                    'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                                    'name' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                                    'percentage' => new external_value(PARAM_FLOAT, 'Value', VALUE_REQUIRED),
                                ])
                            ),
                            'competencies' => new external_multiple_structure(
                                new external_single_structure([
                                    'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                                    'name' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                                    'percentage' => new external_value(PARAM_FLOAT, 'Value', VALUE_REQUIRED),
                                ])
                            ),
                            'rowchanges' => new external_value(PARAM_BOOL, 'Row changes', VALUE_OPTIONAL),
                        ])
                    ),
                ])
            ),
            'columns' => new external_multiple_structure(
                new external_single_structure([
                    'column' => new external_value(PARAM_TEXT, 'Column id', VALUE_REQUIRED),
                    'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
                    'float' => new external_value(PARAM_BOOL, 'Float', VALUE_OPTIONAL),
                    'int' => new external_value(PARAM_BOOL, 'Int', VALUE_OPTIONAL),
                    'text' => new external_value(PARAM_BOOL, 'Text', VALUE_OPTIONAL),
                    'select' => new external_value(PARAM_BOOL, 'Select', VALUE_OPTIONAL),
                    'visible' => new external_value(PARAM_BOOL, 'Visible', VALUE_REQUIRED),
                    'canedit' => new external_value(PARAM_BOOL, 'Admin', VALUE_REQUIRED),
                    'canaddrfc' => new external_value(PARAM_BOOL, 'Can add RFC', VALUE_OPTIONAL),
                    'protected' => new external_value(PARAM_BOOL, 'Protected', VALUE_OPTIONAL),
                    'label' => new external_value(PARAM_TEXT, 'Label', VALUE_REQUIRED),
                    'help' => new external_value(PARAM_TEXT, 'Help text', VALUE_OPTIONAL),
                    'columnid' => new external_value(PARAM_INT, 'Column id', VALUE_REQUIRED),
                    'length' => new external_value(PARAM_INT, 'Length', VALUE_REQUIRED),
                    'field' => new external_value(PARAM_TEXT, 'Field', VALUE_REQUIRED),
                    'sample_value' => new external_value(PARAM_TEXT, 'Sample value', VALUE_REQUIRED),
                    'min' => new external_value(PARAM_INT, 'Min', VALUE_OPTIONAL),
                    'max' => new external_value(PARAM_INT, 'Max', VALUE_OPTIONAL),
                    'sum' => new external_value(PARAM_FLOAT, 'Sum', VALUE_OPTIONAL),
                    'hassum' => new external_value(PARAM_BOOL, 'Has sum', VALUE_OPTIONAL),
                    'newsum' => new external_value(PARAM_FLOAT, 'New sum', VALUE_OPTIONAL),
                    'hasnewsum' => new external_value(PARAM_BOOL, 'Has new sum', VALUE_OPTIONAL),
                    'options' => new external_multiple_structure(
                        new external_single_structure([
                            'name' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                            'selected' => new external_value(PARAM_BOOL, 'Selected', VALUE_REQUIRED),
                        ]),
                        'Option',
                        VALUE_OPTIONAL,
                    ),
                    'group' => new external_value(PARAM_TEXT, 'Group', VALUE_OPTIONAL),
                ])
            ),
            'rfcs' => new external_multiple_structure(
                new external_single_structure([
                    'action' => new external_value(PARAM_TEXT, 'Time modified', VALUE_OPTIONAL),
                    'timecreated' => new external_value(PARAM_INT, 'Time created', VALUE_OPTIONAL),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified', VALUE_OPTIONAL),
                    'userinfo' => new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'UserId', VALUE_REQUIRED),
                        'fullname' => new external_value(PARAM_TEXT, 'New value', VALUE_OPTIONAL),
                    ], 'User Info', VALUE_OPTIONAL),
                ])
            ),
        ]);
    }
}
