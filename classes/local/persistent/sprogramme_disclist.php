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
 * Class sprogramme_disciplines
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_disclist extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_disclist';

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'uniqueid' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'competvet', 'uniqueid'),
            ],
            'type' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'competvet', 'type'),
            ],
            'parent' => [
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'competvet', 'parent'),
            ],
            'name' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'competvet', 'name'),
            ],
            'sortorder' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'competvet', 'sortorder'),
            ],
        ];
    }

    /**
     * Get the sorted list of disciplines.
     * @return array
     */
    public static function get_sorted(): array {
        $headings = self::get_records(['type' => 'heading'], 'sortorder');
        $sorted = [];
        foreach ($headings as $headings) {
            $items = self::get_records(['parent' => $headings->get('uniqueid')], 'sortorder');
            $itemsmap = array_map(function($item) {
                return [
                    'id' => $item->get('id'),
                    'uniqueid' => $item->get('uniqueid'),
                    'type' => $item->get('type'),
                    'parent' => $item->get('parent'),
                    'name' => $item->get('name'),
                    'sortorder' => $item->get('sortorder'),
                ];
            }, $items);
            $sorted[] = [
                'uniqueid' => $headings->get('uniqueid'),
                'type' => $headings->get('type'),
                'parent' => $headings->get('parent'),
                'name' => $headings->get('name'),
                'sortorder' => $headings->get('sortorder'),
                'items' => $itemsmap,
            ];
        }
        return $sorted;
    }
}
