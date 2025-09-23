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
use customfield_sprogramme\local\rfc_manager;
use customfield_sprogramme\utils;

/**
 * Class set_data
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_data extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'datafieldid' => new external_value(PARAM_INT, 'datafieldid', VALUE_DEFAULT, ''),
            'modules' => new external_multiple_structure(
                new external_single_structure([
                    'moduleid' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                    'modulesortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                    'modulename' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                    'deleted' => new external_value(PARAM_BOOL, 'Deleted', VALUE_DEFAULT, false),
                    'rows' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_OPTIONAL),
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
                        ])
                    ),
                ])
            ),
        ]);
    }

    /**
     * Execute and return json data.
     *
     * @param int $datafieldid - The course id
     * @param array $modules - The modules to update
     * @return array The data in JSON format
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $datafieldid, array $modules): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'datafieldid' => $datafieldid,
                'modules' => $modules,
            ]
        );
        $datafieldid = $params['datafieldid'];
        $context = utils::get_context_from_datafieldid($datafieldid);
        self::validate_context($context);
        require_capability('customfield/sprogramme:edit', $context);
        $modules = $params['modules'];

        $programmemanager = new programme_manager($datafieldid);

        if (!$programmemanager->can_edit()) {
            return [
                'data' => "notallowed", // Deal with this better (like a proper exception).
            ];
        }

        $rfc = new rfc_manager($datafieldid);
        if (
            $rfc->is_required()
            && $programmemanager->has_protected_data_changes($modules)
        ) {
            if (!$rfc->can_add()) {
                return [
                    'data' => "cannotaddrfc", // Deal with this better (like a proper exception).
                ];
            }
            $rfc->create($modules);
            $result = 'newrfc';
        } else {
            $result = $programmemanager->set_data($modules);
        }
        $data = [
            'data' => $result,
        ];

        return $data;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'data' => new external_value(PARAM_TEXT, 'The result of the set data'),
        ]);
    }
}
