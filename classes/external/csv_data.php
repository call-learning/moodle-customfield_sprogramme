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
use core_external\external_single_structure;
use core_external\external_value;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\utils;

/**
 * Class csv_data
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_data  extends external_api {

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
     * Execute and return the data in CSV format.
     *
     * @param int $datafieldid - The course id.
     * @return array $data - The data in JSON format
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $datafieldid): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            ['datafieldid' => $datafieldid]
        );
        $datafieldid = $params['datafieldid'];
        $context = utils::get_context_from_datafieldid($datafieldid);
        require_capability('customfield/sprogramme:view', $context);
        $data = [];
        $programmemanager = new programme_manager($datafieldid);
        $data['csv'] = $programmemanager->get_csv_data();
        $courseid = utils::get_instanceid_from_datafieldid($datafieldid);
        $course = get_course($courseid);
        $data['filename'] = $course->shortname . '-' . date('Y-m-d') . '_programme.csv';
        return $data;
    }

    /**
     * Returns description of method return value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'csv' => new external_value(PARAM_TEXT, 'CSVDATA', VALUE_REQUIRED),
            'filename' => new external_value(PARAM_TEXT, 'Filename', VALUE_REQUIRED),
        ]);
    }
}
