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

namespace customfield_sprogramme\local\persistent;

use core\persistent;
use customfield_sprogramme\utils;
use lang_string;

/**
 * Class sprogramme_rfc
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_rfc extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_rfc';

    /**
     * Comment types array
     */
    const CHANGE_TYPES = [
        self::RFC_CANCELLED => 'cancelled',
        self::RFC_REQUESTED => 'requested',
        self::RFC_SUBMITTED => 'submitted',
        self::RFC_ACCEPTED => 'accepted',
        self::RFC_REJECTED => 'rejected',
    ];
    const RFC_CANCELLED = 0;
    /**
     * Request for change submitted.
     */
    const RFC_REQUESTED = 1;
    /**
     * Request for change submitted.
     */
    const RFC_SUBMITTED = 2;
    /**
     * Request for change accepted.
     */
    const RFC_ACCEPTED = 3;
    /**
     * Request for change rejected.
     */
    const RFC_REJECTED = 4;

    // Define properties and methods as needed for the RFC functionality.
    protected static function define_properties() {
        return [
            'courseid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:courseid'),
                'default' => 0,
            ],
            'datafieldid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:datafieldid'),
            ],
            'type' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:type'),
            ],
            'snapshot' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:snapshot'),
            ],
            'adminid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:adminid'),
            ],
        ];
    }

    /**
     * Get all records for a given programme.
     *
     * @param int $datafieldid
     * @return sprogramme_rfc|null
     */
    public static function get_rfc(int $datafieldid): ?sprogramme_rfc {
        global $USER;

        $types = [
            ['type' => self::RFC_REQUESTED, 'adminid' => $USER->id],
            ['type' => self::RFC_SUBMITTED, 'adminid' => $USER->id],
            ['type' => self::RFC_CANCELLED, 'adminid' => $USER->id],
            ['type' => self::RFC_SUBMITTED],
        ];

        foreach ($types as $params) {
            $params['datafieldid'] = $datafieldid;
            $records = self::get_records($params, 'timecreated', 'DESC', 0, 1);
            if ($records) {
                return array_shift($records);
            }
        }
        return null;
    }

    /**
     * Get user rfcs.
     * Users can only provide 1 RFC per datafield at a time, get the RFCS grouped by user sorted by timecreated.
     * Each user can have submitted multiple records per datafield, once these RFCS have self::RFC_SUBMITTED they are send to the
     * admin for approval. So for each datafield each user will need to have 1 line returned from this function.
     * The timemodified should be the latests time modified from this group of rfc records in the table.
     * If no datafieldid is provided, all rfcs will be returned.
     *
     * @param int $datafieldid
     * @param int $type
     * @param int $adminid
     * @param int $start
     * @param int $limit
     * @return array
     */
    public static function get_rfcs(
        int $datafieldid = 0,
        int $type = self::RFC_SUBMITTED,
        $adminid = 0,
        int $start = 0,
        int $limit = 0
    ): array {
        $params = [];
        if ($datafieldid) {
            $params['datafieldid'] = $datafieldid;
        }
        if ($type) {
            $params['type'] = $type;
        }
        if ($adminid) {
            $params['adminid'] = $adminid;
        }
        $allrfcs = self::get_records($params, 'timecreated', 'DESC', $start, $limit);
        $rfcs = [];
        foreach ($allrfcs as $rfcpersistent) {
            $rfc = $rfcpersistent->to_record();
            $rfc->userinfo = utils::get_user_info($rfc->adminid);
            $rfcs[] = $rfc;
        }
        return $rfcs;
    }

    /**
     * Count the number of RFCs for a datafield.
     *
     * @param int $datafieldid
     * @param int $type
     * @param int $adminid
     * @return int
     */
    public static function count_rfc(int $datafieldid = 0, int $type = self::RFC_SUBMITTED, $adminid = 0): int {
        $params = [];
        if ($datafieldid) {
            $params['datafieldid'] = $datafieldid;
        }
        if ($type) {
            $params['type'] = $type;
        }
        if ($adminid) {
            $params['adminid'] = $adminid;
        }
        return self::count_records($params);
    }
}
