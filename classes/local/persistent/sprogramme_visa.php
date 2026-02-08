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
 * Class sprogramme_visa
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_visa extends persistent {
    /**
     * Current table
     */
    public const TABLE = 'customfield_sprogramme_rfc_visa';

    /**
     * Visa status constants PENDING = 2, APPROVED = 1, REJECTED = 0
     */
    public const STATUS_PENDING = 2;
    /**
     * Visa status constants PENDING = 2, APPROVED = 1, REJECTED = 0
     */
    public const STATUS_APPROVED = 1;
    /**
     * Visa status constants PENDING = 2, APPROVED = 1, REJECTED = 0
     */
    public const STATUS_REJECTED = 0;


    #[\Override]
    protected static function define_properties() {
        return [
            'rfcid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:rfcid'),
                'default' => 0,
            ],
            'visauser' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:visauser'),
            ],
            'status' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:status'),
                'default' => 2, // Default to 'pending'.
            ],
            'comment' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:comment'),
            ],
        ];
    }

    /**
     * Get the status as a human-readable string.
     *
     * @return string
     */
    public function get_status_string(): string {
        return match ($this->get('status')) {
            self::STATUS_APPROVED => get_string('approved', 'customfield_sprogramme'),
            self::STATUS_REJECTED => get_string('rejected', 'customfield_sprogramme'),
            default => get_string('pending', 'customfield_sprogramme'),
        };
    }
}
