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
    const TABLE = 'customfield_sprogramme_change';


    /**
     * Comment types array
     */
    const ACTTION_TYPES = [
        self::FIELD_CHANGED => 'changed',
        self::ROW_ADDED => 'rowadded',
        self::ROW_REMOVED => 'rowremoved',
        self::ROW_CHANGED => 'rowchanged',
        self::MODULE_ADDED => 'moduleadded',
        self::MODULE_REMOVED => 'moduleremoved',
        self::MODULE_CHANGED => 'modulechanged',
    ];

    /**
     * Field changed.
     */
    const FIELD_CHANGED = 1;

    /**
     * Row added.
     */
    const ROW_ADDED = 2;

    /**
     * Row removed.
     */
    const ROW_REMOVED = 3;

    /**
     * Row changed.
     */
    const ROW_CHANGED = 4;

    /**
     * Module added.
     */
    const MODULE_ADDED = 5; 

    /**
     * Module removed.
     */
    const MODULE_REMOVED = 6;

    /**
     * Module changed.
     */
    const MODULE_CHANGED = 7;

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'rfcid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:rfcid'),
            ],
            'action' => [
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:action'),
            ],
            'moduleid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:pid'),
            ],
            'pid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:pid'),
            ],
            'sortorder' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:prevpid'),
            ],
            'newvalues' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:newvalue'),
            ],
        ];
    }
}
