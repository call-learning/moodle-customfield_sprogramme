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
use lang_string;

/**
 * Class sprogramme_disc
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_module extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_module';

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
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_module:courseid'),
            ],
            'sortorder' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_module:sortorder'),
            ],
            'name' => [
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_module:name'),
            ],
        ];
    }

    /**
     * Get all records for a given course
     * @param int $courseid
     * @return array
     */
    public static function get_all_records_for_course(int $courseid): array {
        return self::get_records(['courseid' => $courseid], 'sortorder');
    }
}
