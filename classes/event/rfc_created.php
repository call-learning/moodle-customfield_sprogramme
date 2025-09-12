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

namespace customfield_sprogramme\event;

use customfield_sprogramme\local\persistent\sprogramme_rfc;

/**
 * A rfc has been created.
 *
 * @package    customfield_sprogramme
 * @copyright   2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfc_created extends \core\event\base {
    /**
     * Get the name of the event
     * @return string
     */
    public static function get_name() {
        return get_string('event_rfc_submitted', 'customfield_sprogramme');
    }

    /**
     * Get the objectid mapping
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => sprogramme_rfc::TABLE, 'restore' => 'rfc'];
    }

    /**
     * Get the description of the event
     * @return string
     */
    public function get_description() {
        return "The user with id {$this->userid} submitted a rfc with id {$this->objectid}.";
    }

    /**
     * Get the url of the event
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/customfield/field/sprogramme/rfc.php', ['id' => $this->objectid]);
    }

    /**
     * Get the other mapping
     * @return array
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = sprogramme_rfc::TABLE;
    }
}
