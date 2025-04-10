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

namespace customfield_sprogramme\task;
use customfield_sprogramme\local\persistent\notification;
use customfield_sprogramme\local\api\notifications as notificationsapi;
/**
 * Class notifications
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifications extends \core\task\scheduled_task {
    /** @var string Task name */
    private $taskname = 'notifications';

    /**
     * Get the name of the task.
     *
     * @return string Task name shown in admin screens.
     */
    public function get_name() {
        return get_string('notification:' . $this->taskname, 'customfield_sprogramme');
    }

    /**
     * Execute the task sending reminders to students who have items to do.
     */
    public function execute() {
        $notifications = notification::get_notifications(notification::STATUS_PENDING);
        foreach ($notifications as $notification) {
            try {
                $notification->set_status(notification::STATUS_SEND);
                $notification->update();
                notificationsapi::send_email($notification);
            } catch (\Exception $e) {
                debugging("Exception when sending email to user ID {$notification->userid}: " . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }
}
