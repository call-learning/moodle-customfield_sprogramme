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
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, ''),
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

    /**
     * Execute and return json data.
     *
     * @param string $courseid - The course id
     * @param array $rows - The rows to update
     * @return array  - The data in JSON format
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $courseid, array $rows): array {
        $context = context_system::instance();
        require_capability('customfield/sprogramme:edit', $context);
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'courseid' => $courseid,
                'rows' => $rows,
            ]
        );
        self::validate_context($context);
        $courseid = $params['courseid'];
        $rows = $params['rows'];

        programme::set_records($courseid, $rows);

        $data = [
            'data' => 'This is the data for table ' . $courseid,
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
            'data' => new external_value(PARAM_TEXT, 'The data in JSON format'),
        ]);
    }
}
