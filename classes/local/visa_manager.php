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
     * The datafield id for the RFC.
     *
     * @var int The datafield id of the RFC.
     */
    private int $datafieldid;

    /**
     * Constructor
     *
     * @param int $rfcid
     */
    public function __construct(
        /** @var int $rfcid */
        private int $rfcid,
    ) {
        if (!sprogramme_rfc::record_exists($rfcid)) {
            throw new \moodle_exception('invalidrfcid', 'customfield_sprogramme');
        }
        $this->datafieldid = sprogramme_rfc::get_record(['id' => $rfcid])->get('datafieldid');
        $this->context = utils::get_context_from_datafieldid($this->datafieldid) ?? context_system::instance();
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
        global $USER;
        $visas = $this->get_visas();
        $visasbyuser = [];
        foreach ($visas as $visa) {
            $visasbyuser[$visa->get('visauser')] = $visa;
        }

        $reviewers = [];
        $hodreviewersbyid = [];
        if ($this->context && $this->context->contextlevel == CONTEXT_COURSE && !empty($this->context->instanceid)) {
            $reviewers = utils::get_responsible_visa_reviewer_for_course($this->context->instanceid);
            foreach (utils::get_hod_reviewers_for_course($this->context->instanceid) as $hodreviewer) {
                $hodreviewersbyid[$hodreviewer->id] = true;
            }
        }
        $reviewersbyid = [];
        foreach ($reviewers as $reviewer) {
            $reviewersbyid[$reviewer->id] = $reviewer;
        }

        $data = [
            'canvisa' => $this->can_visa($USER->id),
            'rfcid' => $this->rfcid,
            'visas' => [],
            'todo' => $this->get_visas_total_todo(),
            'done' => 0,
            'approved' => 0,
            'rejected' => 0,
        ];

        foreach ($reviewersbyid as $reviewerid => $reviewer) {
            $visa = $visasbyuser[$reviewerid] ?? null;
            $status = $visa ? $visa->get('status') : sprogramme_visa::STATUS_PENDING;
            $comment = $visa ? $visa->get('comment') : '';
            $timemodified = $visa ? $visa->get('timemodified') : 0;

            $data['visas'][] = [
                'id' => $visa ? $visa->get('id') : 0,
                'rfcid' => $this->rfcid,
                'visauser' => [
                    'id' => $reviewer->id,
                    'fullname' => fullname($reviewer),
                    'isdepartmenthead' => !empty($hodreviewersbyid[$reviewer->id]),
                ],
                'statustext' => $visa ? $visa->get_status_string() : get_string('pending', 'customfield_sprogramme'),
                'isapproved' => $status == sprogramme_visa::STATUS_APPROVED,
                'isrejected' => $status == sprogramme_visa::STATUS_REJECTED,
                'ispending' => $status == sprogramme_visa::STATUS_PENDING,
                'showcomment' => $status != sprogramme_visa::STATUS_PENDING && $comment !== '',
                'comment' => $comment,
                'timemodified' => $timemodified,
                'canmanage' => $data['canvisa'] && (int)$USER->id === (int)$reviewer->id,
            ];
            if ($status == sprogramme_visa::STATUS_APPROVED) {
                $data['approved']++;
            } else if ($status == sprogramme_visa::STATUS_REJECTED) {
                $data['rejected']++;
            }
            if ($status != sprogramme_visa::STATUS_PENDING) {
                $data['done']++;
            }
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
        return utils::is_responsible_visa_reviewer($userid, $this->context);
    }

    /**
     * Accept a visa for the current RFC.
     *
     * @param int $userid
     * @param string $comment
     * @return bool
     */
    public function accept_visa(int $userid, string $comment) {
        $visa = $this->get_visa_for_user($userid);
        $visa->set('status', sprogramme_visa::STATUS_APPROVED);
        $visa->set('comment', $comment);
        $visa->set('timemodified', time());
        $visa->update();
        $this->trigger_visa_updated_event($visa);
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
        $visa = $this->get_visa_for_user($userid);
        $visa->set('status', sprogramme_visa::STATUS_REJECTED);
        $visa->set('comment', $comment);
        $visa->update();
        $this->trigger_visa_updated_event($visa);
        return true;
    }

    /**
     * Remove a visa for the current RFC and user.
     *
     * @param int $userid
     * @return bool
     */
    public function remove_visa(int $userid): bool {
        $visa = sprogramme_visa::get_record(['rfcid' => $this->rfcid, 'visauser' => $userid]);
        if (!$visa) {
            return true;
        }
        $visa->delete();
        return true;
    }

    /**
     * Reset all existing visas for the current RFC to pending.
     *
     * @return void
     */
    public function reset_visas_to_pending(): void {
        $visas = $this->get_visas();
        $now = time();
        foreach ($visas as $visa) {
            $visa->set('status', sprogramme_visa::STATUS_PENDING);
            $visa->set('comment', '');
            $visa->set('timemodified', $now);
            $visa->update();
        }
    }

    /**
     * Get the datafield id for the current RFC.
     *
     * @return int
     */
    public function get_datafield_id() {
        return $this->datafieldid;
    }

    /**
     * Trigger an event when a visa is updated.
     *
     * @param sprogramme_visa $visa
     */
    protected function trigger_visa_updated_event(sprogramme_visa $visa) {
        // Now send an event.
        $event = \customfield_sprogramme\event\rfc_visa_updated::create(
            [
                'context' => $this->context,
                'objectid' => $this->rfcid,
                'other' => [
                    'visaid' => $visa->get('id'),
                    'status' => $visa->get('status'),
                    'usercreated' => $visa->get('visauser'),
                ],
            ]
        );
        $event->trigger();
    }

    /**
     * Get or create a visa for a user and the current RFC.
     *
     * @param int $userid
     * @return sprogramme_visa
     */
    private function get_visa_for_user(int $userid): sprogramme_visa {
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

    /**
     * Get the total number of visas that still need to be done for the current RFC. *
     * This is based on the number of responsible users for the course.
     *
     * @return int
     */
    public function get_visas_total_todo() {
        // Count the responsible users.
        if (!$this->context || !$this->context->instanceid || $this->context->contextlevel != CONTEXT_COURSE) {
            return 0;
        }
        $responsibleusers = utils::get_responsible_visa_reviewer_for_course(
            $this->context->instanceid
        );
        $responsibleuserids = array_map(fn($user) => $user->id, $responsibleusers);
        array_unique($responsibleuserids);
        return count($responsibleuserids);
    }

    /**
     * Get the total number of visas that are not yet approved for the current RFC.
     *
     * @return int
     */
    public function get_visas_total_done() {
        $visas = $this->get_visas();
        $total = 0;
        foreach ($visas as $visa) {
            if ($visa->get('status') != sprogramme_visa::STATUS_APPROVED) {
                $total++;
            }
        }
        return $total;
    }
}
