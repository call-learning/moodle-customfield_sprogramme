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
 * Class get_columns
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_columns extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'datafieldid' => new external_value(PARAM_INT, 'Datafieldid', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Execute and return json data.
     *
     * @param int $datafieldid - The course module id
     * @return array $data - The plannings list
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $datafieldid): array {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'datafieldid' => $datafieldid,
            ]
        );
        self::validate_context(utils::get_context_from_datafieldid($params['datafieldid']));
        $programmemanger = new programme_manager($datafieldid);
        $columns = $programmemanger->get_column_structure();
        $canedit = $programmemanger->can_edit();
        return [
            'columns' => $columns,
            'canedit' => $canedit,

        ];
    }

    /**
     * Returns description of method return value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
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
                    'label' => new external_value(PARAM_TEXT, 'Label', VALUE_REQUIRED),
                    'help' => new external_value(PARAM_TEXT, 'Help text', VALUE_OPTIONAL),
                    'columnid' => new external_value(PARAM_INT, 'Column id', VALUE_REQUIRED),
                    'length' => new external_value(PARAM_INT, 'Length', VALUE_REQUIRED),
                    'group' => new external_value(PARAM_TEXT, 'Group', VALUE_OPTIONAL),
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
                        ]), 'Option', VALUE_OPTIONAL
                    ),
                    'group' => new external_value(PARAM_TEXT, 'Group', VALUE_OPTIONAL),

                ])
            ),
            'canedit' => new external_value(PARAM_BOOL, 'Can edit', VALUE_REQUIRED),
        ]);
    }
}
