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
use customfield_sprogramme\utils;

/**
 * Class programme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfc_manager {
    /** @var \context $context The context of the datafield. */
    private \context $context;

    /**
     * Constructor
     *
     * @param int $datafieldid
     */
    public function __construct(
        /** @var int $datafieldid */
        private int $datafieldid,
    ) {
        if (!$datafieldid) {
            throw new \moodle_exception('invaliddatafieldid', 'customfield_sprogramme');
        }
        $this->context = utils::get_context_from_datafieldid($datafieldid) ?? context_system::instance();
    }

    /**
     * Remove a change request
     *
     * @param int $userid
     * @return bool true if removed
     */
    public function remove(int $userid): bool {
        $result = false;
        $records = sprogramme_rfc::get_records(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $userid,
            ]
        );
        foreach ($records as $record) {
            $record->delete();
            $result = true;
        }
        return $result;
    }

    /**
     * Accept a change request by a user
     *
     * @param int $adminid
     * @return bool true if accepted
     */
    public function accept(int $adminid): bool {
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $adminid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ],
            IGNORE_MULTIPLE // We get the first one we find (there should be only one anyway).
        );
        if (!$rfc) {
            return false; // No submitted rfc found for the course and user.
        }
        $snapshot = $rfc->get('snapshot');
        if (!$snapshot) {
            return false; // No snapshot found in the rfc.
        }
        $data = json_decode($snapshot, true);
        if (!$data) {
            return false; // No data found in the snapshot.
        }
        // Set the data for the programme.
        $rfc->set('type', sprogramme_rfc::RFC_ACCEPTED);
        $rfc->save();
        $programme = new programme_manager($this->datafieldid);
        $result = $programme->set_data($data);
        if (!$result) {
            return false; // If setting the data failed, return false.
        }

        return true;
    }

    /**
     * Check if a user can edit the programme (if they have the capability and the programme can be edited)
     *
     * @return bool
     */
    public function can_edit(): bool {
        $programme = new programme_manager($this->datafieldid);
        if (!$programme->can_edit()) {
            return false; // If we cannot edit the programme, return false.
        }
        return true;
    }

    /**
     * Check if a user can accept a rfc for a course (if they have the capability to edit all and can edit)
     *
     * @return bool
     */
    public function can_accept(): bool {
        return
            has_capability('customfield/sprogramme:editall', $this->context);
    }

    /**
     * Check if a user can cancel a rfc for a course
     *
     * @param int $userid The user id that created the rfc
     * @return bool
     */
    public function can_cancel(int $userid = 0) {
        global $USER;
        if (!$userid) {
            $userid = $USER->id;
        }
        if (has_capability('customfield/sprogramme:editall', $this->context)) {
            return true; // If the user has the capability to edit all, they can cancel any rfc.
        }
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $userid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ],
            IGNORE_MULTIPLE // We get the first one we find (there should be only one anyway).
        );
        if ($rfc) {
            return true;
        }
        return false;
    }

    /**
     * Check if a user can add a new rfc for a course
     * (there shoubld be no submitted rfcs yet) and the user has the capability to edit
     *
     * @return bool
     */
    public function can_add(): bool {
        if (!has_capability('customfield/sprogramme:edit', $this->context)) {
            return false;
        }
        if ($this->has_submitted()) {
            return false;
        }
        return true;
    }

    /**
     * Check if a course has submitted rfcs
     *
     * @return bool
     */
    public function has_submitted(): bool {
        $changerecord = $this->get_current();
        if ($changerecord) {
            return true; // If there is a change record for the course, it means there are submitted rfcs.
        }
        return false;
    }

    /**
     * Check if a user can reject a rfc for a course (if they have the capability to edit all)
     *
     * @return bool
     */
    public function can_reject(): bool {
        return has_capability('customfield/sprogramme:editall', $this->context);
    }


    /**
     * Check if a user can remove a rfc for a course (if they have the capability to edit all)
     *
     * @return bool
     */
    public function can_remove(int $userid): bool {
        global $USER;
        if (!$userid) {
            $userid = $USER->id;
        }
        if (has_capability('customfield/sprogramme:editall', $this->context)) {
            return true; // If the user has the capability to edit all, they can cancel any rfc.
        }
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $userid,
            ],
            IGNORE_MULTIPLE // We get the first one we find (there should be only one anyway).
        );
        if ($rfc) {
            return true;
        }
        return false;
    }

    /**
     * Check if a user can submit a rfc for a course
     *
     * @return bool
     */
    public function can_submit() {
        return has_capability('customfield/sprogramme:edit', $this->context);
    }
    /**
     * Get the rfc for a given datafield and user
     *
     * @param int $userid
     * @param mixed $data
     * @return sprogramme_rfc
     */
    public function create(mixed $data): sprogramme_rfc {
        global $USER;
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $USER->id,
                'type' => sprogramme_rfc::RFC_REQUESTED,
            ]
        );

        if (!$rfc) {
            $rfc = new sprogramme_rfc();
            $rfc->set('datafieldid', $this->datafieldid);
            $rfc->set('adminid', intval($USER->id));
            $rfc->set('snapshot', json_encode($data));
            $rfc->set('type', sprogramme_rfc::RFC_REQUESTED);
            $rfc->save();
        } else {
            // If the rfc already exists, update the snapshot.
            $rfc->set('snapshot', json_encode($data));
            $rfc->save();
        }
        return $rfc;
    }

    /**
     * Cancel a change request
     *
     * @param int $userid
     * @return bool true if cancelled
     */
    public function cancel(int $userid): bool {
        $result = false;
        $record = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $userid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ]
        );
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_CANCELLED);
            $record->save();
            $result = true;
        }
        return $result;
    }

    /**
     * Submit a change request
     *
     * @param int $userid
     * @return bool true if submitted
     */
    public function submit(int $userid): bool {
        $record = $this->get_current();
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_SUBMITTED);
            $record->save();
            $result = true;
            $event = \customfield_sprogramme\event\rfc_submitted::create(
                [
                    'context' => $this->context,
                    'objectid' => $record->get('id'),
                    'other' => [
                        'datafieldid' => $this->datafieldid,
                        'adminid' => $userid,
                    ],
                ]
            );
            $event->trigger(); // Trigger the event. It should also take care of notifications.
        } else {
            // If the record does not exist, we cannot submit it.
            return false;
        }
        return $result;
    }

    /**
     * Get the rfc data for a given datafield
     *
     * @return array $data
     */
    public function get_data(): array {
        global $USER;
        $changerecord = $this->get_current();
        if (!$changerecord) {
            return []; // No RFC found for the course.
        }
        $data = [];

        $userid = $changerecord->get('adminid');
        $cansumit = has_capability('customfield/sprogramme:edit', $this->context) && $userid == $USER->id;
        $canaccept = has_capability('customfield/sprogramme:editall', $this->context);
        $issubmitted = $changerecord->get('type') == sprogramme_rfc::RFC_SUBMITTED;

        $data['issubmitted'] = $issubmitted;
        $data['timemodified'] = $changerecord->get('timemodified');
        $data['userinfo'] = utils::get_user_info($userid);
        $data['canaccept'] = $canaccept;
        $data['cansubmit'] = $cansumit && !$issubmitted && !$canaccept;
        $data['cancancel'] = $cansumit && $issubmitted && !$canaccept;
        return $data;
    }

    /**
     * Reject a change request by a user
     *
     * @param int $userid
     */
    public function reject(int $userid): bool {
        $result = false;
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'adminid' => $userid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ]
        );
        if ($rfc) {
            $rfc->set('type', sprogramme_rfc::RFC_REJECTED);
            $rfc->save();
            $result = true;
        }
        return $result;
    }

    /**
     * Check to see if a rfc is required for a given datafield
     *
     * @return bool
     */
    public function is_required(): bool {
        if (has_capability('customfield/sprogramme:editall', $this->context)) {
            return false; // If the user has the capability to edit all, no rfc is required.
        }
        return true;
    }

    /**
     * Get the current rfc for a given datafield
     *
     * @return sprogramme_rfc|null
     */
    public function get_current(): ?sprogramme_rfc {
        return sprogramme_rfc::get_rfc($this->datafieldid);
    }
}
