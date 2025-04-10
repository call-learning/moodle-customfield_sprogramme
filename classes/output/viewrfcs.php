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

namespace customfield_sprogramme\output;

use customfield_sprogramme\local\persistent\notification;
use customfield_sprogramme\api\notifications as notifications_api;
use core\output\named_templatable;
use renderable;
use renderer_base;
use stdClass;
use moodle_url;

/**
 * Generic renderable for the view.
 *
 * @package    customfield_sprogramme
 * @copyright  2023 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewrfcs implements renderable, named_templatable {
    /**
     * @var $courseid The course id.
     */
    protected $courseid;

    /**
     * @var $status The status to display.
     */
    protected $status;

    /**
     * Export this data so it can be used in a mustache template.
     *
     * @param renderer_base $output
     * @return array|array[]|stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $this->before_render();

        $data['selectcourse'] = array_values($this->get_course_select());
        $data['status'] = $this->get_status_select();

        $searchparams = [];
        if ($this->status) {
            $searchparams['status'] = $this->status;
        }

        $notifications = notification::get_records($searchparams);

        $data['notifications'] = [];
        $numpending = 0;
        foreach ($notifications as $notification) {
            $body = $notification->get('body');
            // Get a short version of the body in plain text.
            $shortmessage = strip_tags($body);
            $shortmessage = substr($shortmessage, 0, 30);
            $status = $notification->get('status');
            if ($status === notification::STATUS_PENDING) {
                $numpending++;
            };
            $delete = new moodle_url('/customfield/field/sprogramme/edit.php', array_merge($this->get_url_params(),
                ['delete' => $notification->get('id')]));

            $send = new moodle_url('/customfield/field/sprogramme/edit.php', array_merge($this->get_url_params(),
                ['send' => $notification->get('id')]));

            $data['notifications'][] = [
                'id' => $notification->get('id'),
                'timecreated' => $notification->get('timecreated'),
                'notification' => get_string('notification:' . $notification->get('notification'),
                    'customfield_sprogramme'),
                'shortmessage' => $shortmessage,
                'recipient' => $notification->get('recipient'),
                'subject' => $notification->get('subject'),
                'body' => $body,
                'delete' => $delete->out(),
                'send' => $send->out(),
                'status' => get_string('notification:status:' . notification::STATUS_TYPES[$status], 'customfield_sprogramme'),
                'cansend' => $notification->can_send(),
            ];
        }
        if ($numpending) {
            $data['numpending'] = $numpending;
            $data['sendallurl'] = new moodle_url('/customfield/field/sprogramme/edit.php',
            array_merge($this->get_url_params(), ['sendall' => 1]));
        }
        $data['numnotifications'] = count($notifications);
        if (count($notifications) > 0) {
            $data['deleteallurl'] = new moodle_url('/customfield/field/sprogramme/edit.php',
            array_merge($this->get_url_params(), ['deleteall' => 1]));
        }

        $data['version'] = time();
        $data['debug'] = $CFG->debugdisplay;

        return $data;
    }

    /**
     * Get the competvet selector
     * @return array
     */
    public function get_course_select(): array {
        global $DB;
        $allinstances = $DB->get_records('course', ['visible' => 1], 'fullname ASC');
        // Map these instances to an array with the id as key, the course and the name as value.
        return array_map(function ($instance) {
            return [
                'id' => $instance->id,
                'course' => get_course($instance->id)->fullname,
                'name' => $instance->fullname,
                'selected' => $instance->id == $this->courseid,
                'url' => new moodle_url('/customfield/field/sprogramme/edit.php', ['id' => $instance->id]),
            ];
        }, $allinstances);
    }

    /**
     * Get the status selector
     * @return array
     */
    public function get_status_select(): array {
        $data = [];
        $data[] = [
            'key' => '',
            'name' => get_string('all'),
            'url' => new moodle_url('/customfield/field/sprogramme/edit.php', ['id' => $this->courseid]),
            'selected' => empty($this->status),
        ];
        foreach (notification::STATUS_TYPES as $key => $status) {
            $data[] = [
                'key' => $key,
                'name' => get_string('notification:status:' . $status, 'customfield_sprogramme'),
                'url' => new moodle_url('/customfield/field/sprogramme/edit.php',
                    ['pagetype' => 'viewrfcs', 'id' => $this->courseid, 'status' => $key]),
                'selected' => $key == $this->status,
            ];
        }
        return $data;
    }

    /**
     * Get the url parameters for this renderable.
     * @return array
     */
    public function get_url_params(): array {
        return [
            'pagetype' => 'viewrfcs',
            'c' => $this->courseid,
            'status' => $this->status,
        ];
    }

    /**
     * Perform actions before rendering.
     * @return void
     */
    public function before_render(): void {
        $delete = optional_param('delete', null, PARAM_INT);
        if ($delete) {
            $todelete = notification::get_record(['id' => $delete]);
            $todelete->delete();
        }

        $send = optional_param('send', null, PARAM_INT);
        if ($send) {
            $notification = notification::get_record(['id' => $send]);
            notifications_api::send_email($notification);
        }

        $sendall = optional_param('sendall', null, PARAM_INT);
        if ($sendall) {
            $params = ['courseid' => $this->courseid];
            if ($this->task) {
                $params['notification'] = $this->task;
            }
            $notifications = notification::get_records($params);
            foreach ($notifications as $notification) {
                notifications_api::send_email($notification);
            }
        }

        $deleteall = optional_param('deleteall', null, PARAM_INT);
        if ($deleteall) {
            $params = ['courseid' => $this->courseid];
            if ($this->task) {
                $params['notification'] = $this->task;
            }
            $notifications = notification::get_records($params);
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
    }

    /**
     * Set data for the object.
     *
     * If data is empty we autofill information from the API and the current user.
     * If not, we get the information from the parameters.
     *
     * The idea behind it is to reuse the template in customfield_sprogramme and local_competvet
     *
     * @param mixed ...$data Array containing two elements: $plannings and $planningstats.
     * @return void
     */
    public function set_data(...$data) {
        if (empty($data)) {
            global $PAGE;
            if ($PAGE->context->contextlevel === CONTEXT_MODULE) {
                $PAGE->set_secondary_active_tab('viewrfcs');

                $courseid = optional_param('c', null, PARAM_INT);
                $task = optional_param('task', null, PARAM_RAW);
                $tasks = $this->get_tasks();
                if (!array_key_exists($task, $tasks)) {
                    $task = null;
                }

                $status = optional_param('status', null, PARAM_INT);
                if (!array_key_exists($status, notification::STATUS_TYPES)) {
                    $status = null;
                }

                $cmid = $PAGE->cm->id;
                if (!$courseid) {
                    $competvet = competvet::get_from_context($PAGE->context);
                    $courseid = $competvet->get_instance_id();
                } else {
                    $competvet = competvet::get_from_instance_id($courseid);
                    $cmid = $competvet->get_course_module_id();
                }
                $data = [$courseid, $cmid, $task, $status];
            } else {
                $data = [null];
            }
        }
        [$this->courseid, $this->courseid, $this->task, $this->status] = $data;
    }

    /**
     * Check if current user has access to this page and throw an exception if not.
     *
     * @return void
     */
    public function check_access(): void {
        global $PAGE;
        $context = $PAGE->context;
        if (!has_capability('mod/competvet:candoeverything', $context)) {
            throw new \moodle_exception('noaccess', 'customfield_sprogramme');
        }
    }

    /**
     * Get the template name to use for this renderable.
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'customfield_sprogramme/emails/notifications';
    }

    /**
     * Get the available tasks for the notifications.
     * @return array
     */
    private function get_tasks(): array {
        return [
            'items_todo' => get_string('notification:items_todo', 'customfield_sprogramme'),
            'end_of_planning' => get_string('notification:end_of_planning', 'customfield_sprogramme'),
            'student_graded' => get_string('notification:student_graded', 'customfield_sprogramme'),
            'student_target:eval' => get_string('notification:student_target:eval', 'customfield_sprogramme'),
            'student_target:autoeval' => get_string('notification:student_target:autoeval', 'customfield_sprogramme'),
            'student_target:cert' => get_string('notification:student_target:cert', 'customfield_sprogramme'),
            'student_target:list' => get_string('notification:student_target:list', 'customfield_sprogramme'),
        ];
    }
}
