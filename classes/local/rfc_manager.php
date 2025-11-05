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
                'usercreated' => $userid,
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
     * @param int $usercreated
     * @return bool true if accepted
     */
    public function accept(int $usercreated): bool {
        global $USER;
        $rfc = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'usercreated' => $usercreated,
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
        $rfc->set('adminid', $USER->id);
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
     * @param int $userid
     * @return bool
     */
    public function can_accept(int $userid): bool {
        $changerecord = $this->get_current($userid);
        if (!$changerecord) {
            return false; // No RFC found for the course.
        }
        $canaccept = has_capability('customfield/sprogramme:editall', $this->context);
        $canaccept = $canaccept && $changerecord->get('type') == sprogramme_rfc::RFC_SUBMITTED;
        return $canaccept;
    }

    /**
     * Check if a user can cancel a rfc for a course
     *
     * @param int $userid The user id that created the rfc
     * @return bool
     */
    public function can_cancel(int $userid): bool {
        global $USER;
        $changerecord = $this->get_current($userid);
        if (!$changerecord) {
            return false; // No RFC found for the course.
        }

        $cancancel = has_capability('customfield/sprogramme:edit', $this->context);
        $cancancel = $cancancel && $changerecord->get('type') == sprogramme_rfc::RFC_SUBMITTED;
        $cancancel = $cancancel && (($USER->id == $userid)
                || has_capability('customfield/sprogramme:editall', $this->context));
        $cancancel = $cancancel && $changerecord->get('usercreated') == $userid;
        return $cancancel;
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
     * @param int $userid
     * @return bool
     */
    public function can_reject(int $userid): bool {
        return $this->can_accept($userid); // Same capability as accepting.
    }

    /**
     * Check if a user can remove a rfc for a datafield (if they have the capability to edit all)
     *
     * @param int $userid
     * @return bool
     */
    public function can_remove(int $userid): bool {
        $changerecord = $this->get_current($userid);
        if (!$changerecord) {
            return false; // No RFC found for the course.
        }
        $usercreated = $changerecord->get('usercreated');
        $canremove = has_capability('customfield/sprogramme:edit', $this->context) && $usercreated == $userid;
        return $canremove;
    }

    /**
     * Check if a user can submit a rfc for a course
     *
     * @param int $userid
     * @return bool
     */
    public function can_submit(int $userid): bool {
        global $USER;
        $changerecord = $this->get_current($userid);
        if (!$changerecord) {
            return false; // No RFC found for the course.
        }
        $usercreated = $changerecord->get('usercreated');
        $cansumit = has_capability('customfield/sprogramme:edit', $this->context) && $usercreated == $userid;
        $cansumit = $cansumit && (($USER->id == $userid)
            || has_capability('customfield/sprogramme:editall', $this->context));
        $cansumit = $cansumit && $changerecord->get('type') != sprogramme_rfc::RFC_SUBMITTED;
        return $cansumit;
    }

    /**
     * Get the rfc for a given datafield and user
     *
     * @param mixed $data
     * @return sprogramme_rfc
     * @throws \coding_exception
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
            $rfc->set('usercreated', intval($USER->id));
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
        global $USER;
        $result = false;
        $record = sprogramme_rfc::get_record(
            [
                'datafieldid' => $this->datafieldid,
                'usercreated' => $userid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ]
        );
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_CANCELLED);
            $record->set('adminid', $USER->id);
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
        $record = $this->get_current($userid);
        if ($record) {
            $record->set('type', sprogramme_rfc::RFC_SUBMITTED);
            $record->set('adminid', $userid);
            $record->save();
            $result = true;
            $event = \customfield_sprogramme\event\rfc_submitted::create(
                [
                    'context' => $this->context,
                    'objectid' => $record->get('id'),
                    'other' => [
                        'datafieldid' => $this->datafieldid,
                        'rfcid' => $record->get('id'),
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
        $usercreated = $changerecord->get('usercreated');
        $issubmitted = $changerecord->get('type') == sprogramme_rfc::RFC_SUBMITTED;

        $data['issubmitted'] = $issubmitted;
        $data['timemodified'] = $changerecord->get('timemodified');
        $data['userinfo'] = utils::get_user_info($usercreated);
        $data['canaccept'] = $this->can_accept($USER->id);
        $data['cansubmit'] = $this->can_submit($USER->id);
        $data['cancancel'] = $this->can_cancel($USER->id);
        $data['canremove'] = $this->can_remove($USER->id);
        $data['canreject'] = $this->can_reject($USER->id);
        $data['canadd'] = $this->can_add();
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
                'usercreated' => $userid,
                'type' => sprogramme_rfc::RFC_SUBMITTED,
            ]
        );
        if ($rfc) {
            $rfc->set('type', sprogramme_rfc::RFC_REJECTED);
            $rfc->set('adminid', $userid);
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
     * @param ?int $userid
     * @return sprogramme_rfc|null
     */
    public function get_current(?int $userid = null): ?sprogramme_rfc {
        global $USER;
        return sprogramme_rfc::get_rfc($this->datafieldid, $userid ?? $USER->id);
    }
}
