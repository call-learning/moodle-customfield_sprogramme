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
 * Class create_row
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_row extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, ''),
            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
        ]);
    }

    /**
     * Create a new row
     *
     * @param int $courseid
     * @param int $sortorder
     * @return array
     */
    public static function execute($courseid, $sortorder): array {
        $context = context_system::instance();
        require_capability('customfield/sprogramme:edit', $context);

        $params = self::validate_parameters(self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sortorder' => $sortorder,
            ]
        );

        $rowid = programme::create_row($params['courseid'], $params['sortorder']);
        return ['rowid' => $rowid];
    }

    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_INT, 'rowid');
    }
}
