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

namespace customfield_sprogramme\local\api;

use core_user;
use customfield_sprogramme\local\persistent\notification;
use customfield_sprogramme\utils;
use moodle_url;

/**
 * Class notifications
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifications {
    /**
     * Set the notification for the given planning.
     *
     * @param string $type The type of notification to send.
     * @param int $id The Notification ID.
     * @param int $datafieldid The Data field ID.
     * @param array $context The email template context (planning, students, etc.).
     */
    public static function add_notification(string $type, int $id, int $datafieldid, array $context = []) {
        $context = self::add_global_context($context, $datafieldid);
        // Get the default language for the emails from the Course settings.
        $subject = self::local_get_string('email:' . $type . ':subject', (object) $context);
        $body = self::get_email_body($type, $context);

        $recipients = self::get_recipients();
        $immediateemail = get_config('customfield_sprogramme', 'immediate_email');

        foreach ($recipients as $recipient) {
            try {
                $nf = self::create($type, $id, $datafieldid, $recipient, $subject, $body, notification::STATUS_PENDING);
                if ($immediateemail) {
                    self::send_email($nf);
                }
            } catch (\exception $e) {
                debugging("Exception when sending email to user ID {$recipient->id}: " . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * add global context variables
     *
     * @param array $context
     * @param int $datafieldid
     * @return array
     */
    private static function add_global_context(array $context, int $datafieldid): array {
        $courseid = utils::get_instanceid_from_datafieldid($datafieldid);
        $course = get_course($courseid);
        $programmelink = new moodle_url('/local/envasyllabus/syllabuspage.php', ['id' => $courseid]);
        $context['programmelink'] = $programmelink->out();
        $context['coursename'] = $course->shortname . " - " . $course->fullname;
        return $context;
    }

    /**
     * Local get string function
     * Gets the string from the local language file or the custom setting.
     *
     * @param string $string
     * @param object $context
     * @return string
     */
    private static function local_get_string(string $string, object $context): string {
        $lang = get_config('customfield_sprogramme', 'defaultlang');
        if (empty($lang)) {
            $lang = 'fr';
        }
        $settingname = str_replace(':', '_', $string);
        $setting = get_config('customfield_sprogramme', $settingname . '_' . $lang);
        if (!empty($setting)) {
            return self::process_placeholders($setting, $context);
        }
        $stringmanager = get_string_manager();
        return $stringmanager->get_string($string, 'customfield_sprogramme', $context, $lang);
    }

    /**
     * Get the body of the email notification.
     *
     * @param string $notification
     * @param array $context
     * @return string
     */
    private static function get_email_body(string $notification, array $context): string {
        global $OUTPUT;
        $content = self::local_get_string('email:' . $notification, (object) $context);
        // The logo is a base64 encoded image.
        // TODO: Check this as it seems odd.
        $logo = $OUTPUT->render_from_template('customfield_sprogramme/emails/logo', []);
        return $content . $logo;
    }

    /**
     * Get the recipients of notifications.
     *
     * @return array
     */
    private static function get_recipients(): array {
        $recipients = get_config('customfield_sprogramme', 'approvalemail');
        if (empty(trim($recipients))) {
            return [core_user::get_support_user()->email];
        }
        // Separate the recipients by comma.
        $recipients = explode(',', $recipients);
        $recipients = array_map('trim', $recipients);
        return $recipients;
    }

    /**
     * Create a new notification.
     *
     * @param string $type The type of notification sent.
     * @param int $id The Notification ID.
     * @param int $datafieldid The Course ID.
     * @param string $recipient The email address of the recipient.
     * @param string $subject The email subject.
     * @param string $body The email body content.
     * @param int $status The status of the notification.
     * @return notification
     */
    private static function create(
        string $type,
        int $id,
        int $datafieldid,
        string $recipient,
        string $subject,
        string $body,
        int $status
    ) {
        $nf = new notification(
            0,
            (object) [
                'notification' => $type,
                'notifid' => $id,
                'datafieldid' => $datafieldid,
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'status' => $status,
            ]
        );
        $nf->save();
        return $nf;
    }

    /**
     * Send the email notification to the given recipient.
     *
     * @param notification $notification
     */
    public static function send_email(notification $notification) {
        if ($notification->get('status') == notification::STATUS_SEND) {
            return;
        }
        $noreplyuser = \core_user::get_noreply_user();
        $noreplyuser->email = $notification->get('recipient');
        $subject = $notification->get('subject');
        $body = $notification->get('body');
        $success = email_to_user($noreplyuser, core_user::get_noreply_user(), $subject, $body);
        if (!$success) {
            debugging("Failed to send email to user ID {$noreplyuser->email}", DEBUG_DEVELOPER);
        } else {
            $notification->set('status', notification::STATUS_SEND);
            $notification->save();
        }
    }

    /**
     * Processes a custom string by replacing placeholders with actual values.
     *
     * @param string $string The custom string containing placeholders.
     * @param mixed $a An object or array containing values for placeholders.
     * @return string The processed string with placeholders replaced.
     */
    private static function process_placeholders($string, $a): string {
        if (is_array($a) || is_object($a)) {
            foreach ($a as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }
                $placeholder = '{$a->' . $key . '}';
                $string = str_replace($placeholder, $value, $string);
            }
        } else {
            $string = str_replace('{$a}', $a, $string);
        }
        return $string;
    }
}
