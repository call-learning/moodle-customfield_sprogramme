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
 * Class reject_rfc
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reject_rfc extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, ''),
            'userid' => new external_value(PARAM_INT, 'user id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Reject a RFC
     *
     * @param int $courseid The course id
     * @param int $userid The user id
     * @return bool
     */
    public static function execute($courseid, $userid): bool {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'courseid' => $courseid,
                'userid' => $userid,
            ]);
        $courseid = $params['courseid'];
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('customfield/sprogramme:editall', $context);

        return programme::reject_rfc($params['courseid'], $params['userid']);
    }
    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'true on success, false on failure');
    }
}
