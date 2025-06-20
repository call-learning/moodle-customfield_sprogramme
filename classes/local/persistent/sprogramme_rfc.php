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

use context_course;
use core\persistent;
use lang_string;
use user_picture;
use core_user;
use moodle_exception;

/**
 * Class sprogramme_rfc
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme_rfc extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_rfc';

    /**
     * Comment types array
     */
    const CHANGE_TYPES = [
        self::RFC_REQUESTED => 'requested',
        self::RFC_SUBMITTED => 'submitted',
        self::RFC_ACCEPTED => 'accepted',
        self::RFC_REJECTED => 'rejected',
    ];
    /**
     * Request for change submitted.
     */
    const RFC_REQUESTED = 1;
    /**
     * Request for change submitted.
     */
    const RFC_SUBMITTED = 2;
    /**
     * Request for change accepted.
     */
    const RFC_ACCEPTED = 3;
    /**
     * Request for change rejected.
     */
    const RFC_REJECTED = 4;

    // Define properties and methods as needed for the RFC functionality.
    protected static function define_properties() {
        return [
            'courseid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:courseid'),
            ],
            'type' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:type'),
            ],
            'snapshot' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:snapshot'),
            ],
            'adminid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme_comp:adminid'),
            ],
        ];
    }

        /**
     * Get all records for a given programme.
     * @param int $courseid
     * @return sprogramme_rfc|null
     */
    public static function get_rfc(int $courseid): ?sprogramme_rfc {
        global $USER;
        $context = context_course::instance($courseid);
        $editall = has_capability('customfield/sprogramme:editall', $context);
        if ($editall) {
            $record =  self::get_record(['courseid' => $courseid, 'type' => self::RFC_SUBMITTED]);
            return $record ?: null;
        } else {
            $submitted = self::get_record(['courseid' => $courseid, 'type' => self::RFC_REQUESTED, 'usermodified' => $USER->id]);
            if ($submitted) {
                return $submitted;
            }
            $requested = self::get_record(['courseid' => $courseid, 'type' => self::RFC_SUBMITTED, 'usermodified' => $USER->id]);
            if ($requested) {
                return $requested;
            }
        }
        return null;
    }

    /**
     * Get user rfcs.
     * Users can only provide 1 RFC per course at a time, get the RFCS grouped by user sorted by timecreated.
     * Each user can have submitted multiple records per course, once these RFCS have self::RFC_SUBMITTED they are send to the
     * admin for approval. So for each course each user will need to have 1 line returned from this function.
     * The timemodified should be the latests time modified from this group of rfc records in the table.
     * If no courseid is provided, all rfcs will be returned.
     *
     * @param int $courseid
     * @param int $type
     * @param int $adminid
     * @return array
     */
    public static function get_course_rfcs(int $courseid = 0, int $type = self::RFC_SUBMITTED, $adminid = 0): array {
        $params = [];
        if ($courseid) {
            $params['courseid'] = $courseid;
        }
        if ($type) {
            $params['type'] = $type;
        }
        if ($adminid) {
            $params['adminid'] = $adminid;
        }
        $allrfcs = self::get_records($params);
        $rfcs = [];
        foreach ($allrfcs as $rfcpersistent) {
            $rfc = $rfcpersistent->to_record();
            $rfc->userinfo = self::get_user_info($rfc->adminid);
            $rfc->course = get_course($rfc->courseid);
            $rfcs[] = $rfc;
        }
        return $rfcs;
    }

    /**
     * Get user information (picture and fullname) for the given user id.
     *
     * @param int $userid The ID of the user.
     * @return array associative array with id, fullname and userpictureurl.
     */
    public static function get_user_info(int $userid): array {
        global $PAGE;
        $user = core_user::get_user($userid);
        if (!$user) {
            $renderer = $PAGE->get_renderer('core');
            return [
                'id' => $userid,
                'fullname' => get_string('usernotfound', 'customfield_sprogramme'),
                'userpictureurl' => $renderer->image_url('u/f1')->out(false), // Default image.
                'firstname' => 'firstname',
                'lastname' => 'lastname',
            ];
        }
        $userpicture = new user_picture($user);
        $userpicture->includetoken = true;
        $userpicture->size = 1; // Size f1.
        return [
            'id' => $userid,
            'fullname' => fullname($user),
            'email' => $user->email,
            'userpictureurl' => $userpicture->get_url($PAGE)->out(false),
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        ];
    }

    /**
     * Get all unique course ids from the RFCs.
     * @param int $status
     * @return array
     */
    public static function get_all_course_ids(int $status = self::RFC_SUBMITTED): array {
        $records = self::get_records(['type' => $status]);
        $courseids = [];
        foreach ($records as $record) {
            if (!in_array($record->get('courseid'), $courseids)) {
                $courseids[] = $record->get('courseid');
            }
        }
        return $courseids;
    }
}
