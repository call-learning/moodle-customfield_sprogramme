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

use customfield_sprogramme\event\rfc_accepted;
use customfield_sprogramme\event\rfc_created;
use customfield_sprogramme\event\rfc_submitted;
use customfield_sprogramme\event\rfc_visa_updated;
use customfield_sprogramme\local\api\notifications;
use customfield_sprogramme\local\visa_manager;

/**
 * Monitor event related to rfc (request for change)
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfc_visa_observer {

    /**
     * An rfc has been created.
     *
     * @param rfc_visa_updated $event
     */
    public static function rfc_visa_updated(rfc_visa_updated $event): void {
        $eventdata = $event->get_data();
        $userid = $eventdata['userid'];
        $rfcid = $eventdata['objectid'];
        $visamanager = new visa_manager($rfcid);
        $visastodo = $visamanager->get_visas_total_todo();
        $visadone = $visamanager->get_visas_total_done();
        if ($visadone >= $visastodo) {
            $datafieldid = $visamanager->get_datafield_id();
            notifications::add_notification('rfc_visa_all_done', $userid, $datafieldid);
        }
    }
}
