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

namespace customfield_sprogramme\local;

use context_system;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\persistent\sprogramme_visa;
use customfield_sprogramme\utils;

/**
 * Class programme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visa_manager {
    /**
     * The context identifier for the RFC.
     *
    * @var context The context of the RFC.
    */
    private \context $context;

    /**
     * Constructor
     *
     * @param int $rfcid
     */
    public function __construct(
        /** @var int $rfcid */
        private int $rfcid,
    ) {
        if (!sprogramme_rfc::record_exists($rfcid) ) {
            throw new \moodle_exception('invalidrfcid', 'customfield_sprogramme');
        }
        $datafieldid = sprogramme_rfc::get_record(['id' => $rfcid])->get('datafieldid');
        $this->context = utils::get_context_from_datafieldid($datafieldid) ?? context_system::instance();
    }

    /**
     * Get all visas for the current RFC.
     *
     * @return sprogramme_visa[]
     */
    public function get_visas(): array {
        return sprogramme_visa::get_records(['rfcid' => $this->rfcid]);
    }

    /**
     * Get all visas for the current RFC formatted for output.
     *
     * @return array
     */
    public function get_visa_data(): array {
        $visas = $this->get_visas();
        $data = [];
        foreach ($visas as $visa) {
            $visauser = \core_user::get_user($visa->get('visauser'));

            $data[] = [
                'id' => $visa->get('id'),
                'rfcid' => $visa->get('rfcid'),
                'visauser' => [
                    'id' => $visauser->id,
                    'fullname' => fullname($visauser),
                ],
                'status' => $visa->get('status'),
                'statustext' => $visa->get_status_string(),
                'timemodified' => $visa->get('timemodified'),
            ];
        }
        return $data;
    }

    /**
     * Check if a user can visa the current RFC.
     *
     * @param int $userid
     * @return bool
     */
    public function can_visa($userid): bool {
        return utils::is_responsible($userid, $this->context);
    }

    /**
     * Accept a visa for the current RFC.
     *
     * @param int $userid
     * @param string $comment
     * @return bool
     */
    public function accept_visa(int $userid, string $comment) {
        $visa = $this->get_visa($userid);
        $visa->set('status', sprogramme_visa::STATUS_APPROVED);
        $visa->set('comment', $comment);
        $visa->update();
        return true;
    }
    /**
     * Accept a visa for the current RFC.
     *
     * @param int $userid
     * @param string $comment
     * @return bool
     */
    public function reject_visa(int $userid, string $comment) {
        $visa = $this->get_visa($userid);
        $visa->set('status', sprogramme_visa::STATUS_REJECTED);
        $visa->set('comment', $comment);
        $visa->update();
        return true;
    }

    /**
     * Get or create a visa for a user and the current RFC.
     *
     * @param int $userid
     * @return sprogramme_visa
     */
    private function get_visa(int $userid): sprogramme_visa {
        $visa = sprogramme_visa::get_record(['rfcid' => $this->rfcid, 'visauser' => $userid]);
        if (!$visa) {
            $visa = new sprogramme_visa(0, (object) [
                'rfcid' => $this->rfcid,
                'visauser' => $userid,
                'comment' => '',
            ]);
            $visa->create();
        }
        return $visa;
    }
}