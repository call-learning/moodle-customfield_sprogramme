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
use customfield_sprogramme\local\visa_manager;
use customfield_sprogramme\utils;

/**
 * Class remove_visa
 *
 * @package    customfield_sprogramme
 * @copyright  2026 CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remove_visa extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'rfcid' => new external_value(PARAM_INT, 'rfcid', VALUE_REQUIRED, ''),
        ]);
    }

    /**
     * Remove own visa for a RFC.
     *
     * @param int $rfcid
     * @return bool
     */
    public static function execute(int $rfcid): bool {
        global $USER;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'rfcid' => $rfcid,
            ]
        );
        $rfc = \customfield_sprogramme\local\persistent\sprogramme_rfc::get_record(
            ['id' => $params['rfcid']],
            IGNORE_MISSING
        );
        if (!$rfc) {
            throw new \invalid_parameter_exception('Invalid RFC id');
        }
        $datafieldid = $rfc->get('datafieldid');
        $context = utils::get_context_from_datafieldid($datafieldid);
        self::validate_context($context);
        $visa = new visa_manager($rfc->get('id'));
        if (!$visa->can_visa($USER->id)) {
            throw new \moodle_exception('visaeditionnotallowed', 'customfield_sprogramme');
        }
        return $visa->remove_visa($USER->id);
    }

    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'Removed');
    }
}
