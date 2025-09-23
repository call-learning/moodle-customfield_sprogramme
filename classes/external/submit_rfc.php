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
use core_external\external_value;
use customfield_sprogramme\local\rfc_manager;
use customfield_sprogramme\utils;

/**
 * Class submit_rfc
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit_rfc extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'datafieldid' => new external_value(PARAM_INT, 'datafieldid', VALUE_DEFAULT, ''),
            'userid' => new external_value(PARAM_INT, 'user id', VALUE_REQUIRED),
        ]);
    }
    /**
     * Submit a RFC
     *
     * @param int $datafieldid The course id
     * @param int $userid The user id
     * @return bool
     */
    public static function execute(int $datafieldid, int $userid): bool {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'datafieldid' => $datafieldid,
                'userid' => $userid,
            ]
        );
        $datafieldid = $params['datafieldid'];
        $context = utils::get_context_from_datafieldid($datafieldid);
        self::validate_context($context);
        $rfcmanager = new rfc_manager($datafieldid);
        $rfc = new rfc_manager($datafieldid);
        if (!$rfc->can_submit($params['userid'])) {
            throw new \moodle_exception('rfcsubmissionnotallowed', 'customfield_sprogramme');
        }
        return $rfcmanager->submit($params['userid']);
    }
    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'true on success');
    }
}
