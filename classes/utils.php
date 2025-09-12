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
        $cache = \cache::make('customfield_sprogramme', 'instancebdatafieldid');
        if ($cache->has($datafieldid)) {
            return $cache->get($datafieldid) ?: null;
        }
        $cfd = \core_customfield\data_controller::create($datafieldid);
        $instanceid = $cfd ? $cfd->get('instanceid') : 0;
        $cache->set($datafieldid, $instanceid);

        return $instanceid ?: null;
    }

    /**
     * Get the context associated with a specific custom field data ID.
     *
     * @param int $datafieldid The custom field data ID.
     * @return ?\context the context attached to this datafield
     */
    public static function get_context_from_datafieldid(int $datafieldid): ?\context {
        $instanceid = self::get_instanceid_from_datafieldid($datafieldid);
        if ($instanceid) {
            return \context_course::instance($instanceid);
        }
        return null;
    }
}