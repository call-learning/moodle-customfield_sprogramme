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
     * @param int $userid The Notification user ID.
     * @param int $datafieldid The Data field ID.
     * @param array $context The email template context (planning, students, etc.).
     */
    public static function add_notification(string $type, int $userid, int $datafieldid, array $context = []) {
        $context = self::add_global_context($context, $datafieldid, $userid);
        // Get the default language for the emails from the Course settings.
        $subject = self::local_get_string('email:' . $type . ':subject', (object) $context);
        $body = self::get_email_body($type, $context);
        $recipients = self::get_recipients($type, $datafieldid, $context);

        foreach ($recipients as $recipient) {
            try {
                $nf = new notification(
                    0,
                    (object) [
                        'notification' => $type,
                        'userid' => $userid,
                        'datafieldid' => $datafieldid,
                        'recipient' => $recipient,
                        'subject' => $subject,
                        'body' => $body,
                        'status' => notification::STATUS_PENDING,
                    ]
                );
                $nf->save();
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
     * @param int $userid
     * @return array
     */
    private static function add_global_context(array $context, int $datafieldid, int $userid): array {
        $courseid = utils::get_instanceid_from_datafieldid($datafieldid);
        $course = get_course($courseid);
        $programmelink = new moodle_url('/local/envasyllabus/syllabuspage.php', ['id' => $courseid]);
        $context['programmelink'] = $programmelink->out();
        $context['coursename'] = $course->shortname . " - " . $course->fullname;
        $context['department'] = self::get_department_for_course($courseid);
        $context['responsibles'] = implode(', ', array_map(function ($user) {
            return fullname($user);
        }, self::get_responsible_for_course($courseid)));
        if (isset($context['usercreated'])) {
            $user = core_user::get_user($context['usercreated']);
            $context['requester'] = fullname($user);
        }
        $context['userid'] = $userid;
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
        $content = self::local_get_string('email:' . $notification, (object) $context);
        return $content;
    }

    /**
     * Get the recipients of notifications.
     *
     * @param string $type The type of notification to send.
     * @param int $datafieldid
     * @param array $context
     * @return array
     */
    private static function get_recipients(string $type, int $datafieldid, array $context): array {
        $courseid = utils::get_instanceid_from_datafieldid($datafieldid);
        $approveremails = get_config('customfield_sprogramme', 'approvalemail');
        if (empty(trim($approveremails))) {
            return [core_user::get_support_user()->email];
        }
        // Separate the recipients by comma.
        $approveremails = explode(',', $approveremails);
        $approveremails = array_map('trim', $approveremails);

        // Now get the responsibles for courses.
        $responsibles = self::get_responsible_for_course($courseid);
        $responsibleemails = [];
        foreach ($responsibles as $responsible) {
            $responsibleemails[] = $responsible->email;
        }
        $emails = [];
        switch ($type) {
            case 'rfc_submitted':
                // For RFC submitted, we want to send the email to the approvers and the responsibles.
                $emails = array_merge($approveremails, $responsibleemails);
                break;
            case 'rfc_accepted':
                // For RFC accepted, we also want to send the email to the user who submitted the RFC.
                $emails = array_merge($approveremails, $responsibleemails);
                if (isset($context['usercreated'])) {
                    $user = core_user::get_user($context['usercreated']);
                    if ($user->email && !in_array($user->email, $emails)) {
                        $emails[] = $user->email;
                    }
                }
                break;
        }
        return array_unique($emails);
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

    /**
     * Get users matching the responsible role.
     *
     * Note this is a duplicate of the get_responsible_for_course function in local_envasyllabus
     * but we want to avoid a dependency on the local_envasyllabus plugin in the notifications class.
     *
     * @param int $courseid
     * @return array
     */
    protected static function get_responsible_for_course(int $courseid): array {
        global $DB;
        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $teacherroles = $DB->get_fieldset(
            'role',
            'id',
            ['shortname' => $responsiblerolename]
        );
        if (!empty($teacherroles)) {
            $userfieldsapi = \core_user\fields::for_userpic()->including('username', 'deleted');
            $userfields = 'ra.id, u.id, u.username' . $userfieldsapi->get_sql('u')->selects;
            return get_role_users($teacherroles, \context_course::instance($courseid), true, $userfields);
        } else {
            return [];
        }
    }

    /**
     * Get users matching the responsible role.
     *
     * We assume here that the department is stored in a custom field with
     * shortname 'uc_departement' at the course level, but this can be adapted if needed.
     *
     * @param int $courseid
     * @return string
     */
    protected static function get_department_for_course(int $courseid): string {
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $cfdata = $handler->get_instance_data($courseid, true);
        $customfieldname = get_config('customfield_sprogramme', 'departmentcustomfieldname') ?: 'uc_departement';
        foreach ($cfdata as $cfdatacontroller) {
            $shortname = $cfdatacontroller->get_field()->get('shortname');
            if ($shortname === $customfieldname) {
                return $cfdatacontroller->export_value() ?? '';
            }
        }
        return '';
    }
}
