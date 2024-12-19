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

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;

use customfield_sprogramme\local\api\programme;

/**
 * Class get_data
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_data extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Courseid', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Execute and return json data.
     *
     * @param int $courseid - The course id.
     * @return array $data - The data in JSON format
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $courseid): array {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'courseid' => $courseid,
            ]
        );
        self::validate_context(context_system::instance());
        $courseid = $params['courseid'];

        $data = [
            'rows' => programme::get_data($courseid),
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
            'rows' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                    'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                    'cells' => new external_multiple_structure(
                        new external_single_structure([
                            'column' => new external_value(PARAM_TEXT, 'Column id', VALUE_REQUIRED),
                            'value' => new external_value(PARAM_TEXT, 'Value', VALUE_REQUIRED),
                            'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
                        ])
                    ),
                ])
            ),
        ]);
    }
}

