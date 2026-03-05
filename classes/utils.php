<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace customfield_sprogramme;

use core\context;
use core\output\user_picture;
use core_user;

/**
 * Set of utility functions for the customfield_sprogramme plugin.
 *
 * @package     customfield_sprogramme
 * @category    admin
 * @copyright   2025 CALL Learning - Laurent David <laurent@call-learning>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
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
     * Get the instanceid associated with a specific custom field data ID.
     *
     * @param int $datafieldid The custom field data ID.
     * @return ?int instancedid the instance id attached to this datafield
     */
    public static function get_instanceid_from_datafieldid(int $datafieldid): ?int {
        global $DB;
        if (!$datafieldid) {
            return null;
        }
        $cache = \cache::make('customfield_sprogramme', 'instancebdatafieldid');
        if ($cache->has($datafieldid)) {
            return $cache->get($datafieldid) ?: null;
        }
        try {
            // Do not call any constructor of customfield classes, they may call other code recursively.
            $datafield = $DB->get_record('customfield_data', ['id' => $datafieldid], 'id, instanceid', MUST_EXIST);
        } catch (\dml_missing_record_exception $e) {
            return null;
        }
        $cache->set($datafield->id, $datafield->instanceid ?? 0);

        return $datafield->instanceid ?: null;
    }

    /**
     * Get the context associated with a specific custom field data ID.
     *
     * @param int $datafieldid The custom field data ID.
     * @return ?\context the context attached to this datafield
     */
    public static function get_context_from_datafieldid(int $datafieldid): ?\context {
        if (!$datafieldid) {
            return null;
        }
        $instanceid = self::get_instanceid_from_datafieldid($datafieldid);
        if ($instanceid) {
            return \context_course::instance($instanceid);
        }
        return null;
    }

    /**
     * Get users matching the head of department role.
     *
     * Note this is a duplicate of the get_responsible_for_course function in local_envasyllabus
     * but we want to avoid a dependency on the local_envasyllabus plugin in the notifications class.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_head_of_department_for_course(int $courseid): array {
        $responsibleroleid = self::get_responsible_role_id();
        if (!empty($responsibleroleid)) {
            $userfieldsapi = \core_user\fields::for_userpic()->including('username', 'deleted');
            $userfields = 'ra.id, u.id, u.username' . $userfieldsapi->get_sql('u')->selects;
            return get_role_users($responsibleroleid, \context_course::instance($courseid), true, $userfields);
        } else {
            return [];
        }
    }

    /**
     * Get users matching the role of the person who can add an approval/visa to the changes made on the syllabus
     *
     * Note this is not exactly simular to the get_responsible_for_course function in local_envasyllabus as
     * we add a new set of role like head of department role that can also be responsible for approving the changes on the syllabus.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_responsible_visa_reviewer_for_course(int $courseid): array {
        $reviewers = array_merge(
            self::get_responsible_reviewers_for_course($courseid),
            self::get_hod_reviewers_for_course($courseid)
        );
        $reviewersbyid = [];
        foreach ($reviewers as $reviewer) {
            $reviewersbyid[$reviewer->id] = $reviewer;
        }
        return array_values($reviewersbyid);
    }

    /**
     * Get users matching the responsible reviewer role for a course.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_responsible_reviewers_for_course(int $courseid): array {
        $responsibleroleid = self::get_responsible_role_id();
        if (empty($responsibleroleid)) {
            return [];
        }
        $userfieldsapi = \core_user\fields::for_userpic()->including('username', 'deleted');
        $userfields = 'ra.id, u.id, u.username' . $userfieldsapi->get_sql('u')->selects;
        return get_role_users(
            $responsibleroleid,
            \context_course::instance($courseid),
            true,
            $userfields
        );
    }

    /**
     * Get users matching the head of department reviewer role for a course.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_hod_reviewers_for_course(int $courseid): array {
        $headofdepartmentroleid = self::get_hod_role_id();
        if (empty($headofdepartmentroleid)) {
            return [];
        }
        $userfieldsapi = \core_user\fields::for_userpic()->including('username', 'deleted');
        $userfields = 'ra.id, u.id, u.username' . $userfieldsapi->get_sql('u')->selects;
        return get_role_users(
            $headofdepartmentroleid,
            \context_course::instance($courseid),
            true,
            $userfields
        );
    }

    /**
     * Check if a user can review/approve the changes made on the syllabus based on the responsible
     * role or head of department role assignment in the given context.
     *
     * @param int $userid The ID of the user to check.
     * @param context $context The context to check against.
     * @return bool True if the user has the responsible role, false otherwise.
     */
    public static function is_responsible_visa_reviewer(int $userid, context $context): bool {
        $responsibleroleid = self::get_responsible_role_id();
        $headofdepartmentroleid = self::get_hod_role_id();
        return user_has_role_assignment($userid, $responsibleroleid, $context->id) ||
            user_has_role_assignment($userid, $headofdepartmentroleid, $context->id);
    }

    /**
     * Get the ID of the responsible role from the plugin settings.
     *
     * @return int|null The ID of the responsible role, or null if not found.
     */
    protected static function get_responsible_role_id(): ?int {
        global $DB;
        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        return $DB->get_field(
            'role',
            'id',
            ['shortname' => $responsiblerolename]
        );
    }

    /**
     * Get the ID of the head of department role from the plugin settings.
     *
     * @return int|null The ID of the responsible role, or null if not found.
     */
    protected static function get_hod_role_id(): ?int {
        global $DB;
        $headofdepartmentrolename = get_config('customfield_sprogramme', 'departmentheadrolename');
        return $DB->get_field(
            'role',
            'id',
            ['shortname' => $headofdepartmentrolename]
        );
    }
}
