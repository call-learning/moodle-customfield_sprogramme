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

use context_course;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

use customfield_sprogramme\local\api\programme;

/**
 * Class update_sort_order
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_sort_order extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, ''),
            'moduleid' => new external_value(PARAM_INT, 'moduleid', VALUE_DEFAULT, ''),
            'id' => new external_value(PARAM_INT, 'row id', VALUE_REQUIRED),
            'previd' => new external_value(PARAM_INT, 'Previous row id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Update the sort order of a row
     * @param string $type
     * @param int $courseid
     * @param int $moduleid
     * @param int $id
     * @param int $previd
     * @return bool
     */
    public static function execute($type, $courseid, $moduleid, $id, $previd): bool {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'type' => $type,
                'courseid' => $courseid,
                'moduleid' => $moduleid,
                'id' => $id,
                'previd' => $previd,
            ]);
        $courseid = $params['courseid'];
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('customfield/sprogramme:edit', $context);

        $programme = new programme($courseid);
        $programme->update_sort_order($params['type'], $params['moduleid'], $params['id'], $params['previd']);
        return true;
    }

    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'status');
    }
}
