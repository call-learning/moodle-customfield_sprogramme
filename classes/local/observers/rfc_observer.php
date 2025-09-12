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

namespace customfield_sprogramme\local\observers;

use customfield_sprogramme\event\rfc_created;
use customfield_sprogramme\event\rfc_submitted;
use customfield_sprogramme\local\api\notifications;

/**
 * Monitor event related to rfc (request for change)
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfc_observer {
    /**
     * An rfc has been created.
     *
     * @param rfc_created $event
     */
    public static function rfc_created(rfc_created $event): void {

    }

    /**
     * An rfc has been created.
     *
     * @param rfc_submitted $event
     */
    public static function rfc_submitted(rfc_submitted $event): void {
        $eventdata = $event->get_data();
        $userid = $eventdata['userid'];
        $datafieldid = $eventdata['other']['datafieldid'];
        notifications::add_notification('rfc', $userid, $datafieldid);
    }

}
