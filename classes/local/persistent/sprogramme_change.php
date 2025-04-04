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

use context_course;
use core\persistent;
use lang_string;

/**
 * Class sprogramme_comp
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_change extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_changes';

    /**
     * Comment types array
     */
    const CHANGE_TYPES = [
        self::RFC_REQUESTED => 'requested',
        self::RFC_SUBMITTED => 'submitted',
        self::RFC_ACCEPTED => 'accepted',
        self::RFC_REJECTED => 'rejected',
    ];
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

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'courseid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:courseid'),
            ],
            'pid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:pid'),
            ],
            'newrowid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:newrowid'),
            ],
            'action' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:action'),
            ],
            'field' => [
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:field'),
            ],
            'oldvalue' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:oldvalue'),
            ],
            'newvalue' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:newvalue'),
            ],
            'snapshot' => [
                'type' => PARAM_TEXT,
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
     * @param int $courseid
     * @return array
     */
    public static function get_all_records_for_course(int $courseid): array {
        global $USER;
        $context = context_course::instance($courseid);
        $editall = has_capability('customfield/sprogramme:editall', $context);
        if ($editall) {
            return self::get_records(['courseid' => $courseid, 'action' => self::RFC_SUBMITTED]);
        } else {
            $submitted = self::get_records(['courseid' => $courseid, 'action' => self::RFC_REQUESTED, 'usermodified' => $USER->id]);
            $requested = self::get_records(['courseid' => $courseid, 'action' => self::RFC_SUBMITTED, 'usermodified' => $USER->id]);
            return array_merge($submitted, $requested);
        }
    }
}
