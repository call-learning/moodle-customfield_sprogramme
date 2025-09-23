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

declare(strict_types=1);

namespace customfield_sprogramme\reportbuilder\local\helpers;

use core\output\html_writer;
use stdClass;

/**
 * Class containing helper methods for formatting column data via callbacks
 *
 * @package     customfield_sprogramme
 * @copyright   2025 Laurent David - CALL Learning <call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format {
    /**
     * Returns formatted RFC element.
     *
     * @param mixed $value Encoded RFC string (JSON)
     * @param stdClass $row
     * @return string
     */
    public static function format_rfc_column(mixed $value, stdClass $row): string {
        if (empty($value) && empty($row->oldvalue)) {
            return '';
        }
        $displayvalue = $value ?? '';
        $displayvalue = html_writer::span($displayvalue);
        if (!empty($row->oldvalue)) {
            $displayvalue .= html_writer::tag('strong', " ({$row->oldvalue})");;
        }
        return html_writer::div($displayvalue);
    }
}
