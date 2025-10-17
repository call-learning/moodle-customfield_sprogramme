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

use core\persistent;
use core_user;
use lang_string;

/**
 * Class notification
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme_notification';

    /**
     * Status of the notification pending.
     */
    const STATUS_PENDING = 2;

    /**
     * Status of the notification send.
     */
    const STATUS_SEND = 1;

    /**
     * Notification types array
     */
    const STATUS_TYPES = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_SEND => 'send',
    ];

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'userid' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:userid'),
            ],
            'courseid' => [
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:courseid'),
                'default' => 0,
            ],
            'datafieldid' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification;datafieldid'),
            ],
            'recipient' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:recipient'),
            ],
            'notification' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:notification'),
            ],
            'subject' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:subject'),
            ],
            'body' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_RAW,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:body'),
            ],
            'status' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'notification:status'),
            ],
        ];
    }

    /**
     * Hook to execute before a create operation.
     *
     * Throws an exception if the grid already exists (by idnumber).
     *
     * @return void
     */
    protected function before_create() {
        // Delete notifications older than 30 days.
        $this->delete_old_notifications();
    }

    /**
     * Delete notifications older than 30 days.
     */
    private function delete_old_notifications() {
        global $DB;
        $DB->delete_records_select(self::TABLE, 'timecreated < :time', ['time' => strtotime('-30 days')]);
    }

    /**
     * If this notification can be send.
     * @return bool
     */
    public function can_send(): bool {
        $emailsenabled = get_config('customfield_sprogramme', 'emailsenabled');
        if (empty($emailsenabled)) {
            return false;
        }
        return $this->get('status') === self::STATUS_PENDING;
    }

    /**
     * Send the notification email.
     */
    public function send() {
        if (!$this->can_send()) {
            return;
        }
        $noreplyuser = core_user::get_noreply_user();
        $noreplyuser->email = $this->raw_get('recipient');
        $subject = $this->raw_get('subject');
        $body = $this->raw_get('body');
        $success = email_to_user($noreplyuser, core_user::get_noreply_user(), $subject, $body);
        if (!$success) {
            debugging("Failed to send email to user ID {$noreplyuser->email}", DEBUG_DEVELOPER);
        } else {
            $this->raw_set('status', self::STATUS_SEND);
            $this->save();
        }
    }
}
