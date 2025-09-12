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

use core\output\named_templatable;
use customfield_sprogramme\local\api\notifications;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\utils;
use moodle_url;
use paging_bar;
use renderable;
use renderer_base;
use stdClass;

/**
 * Generic renderable for the view.
 *
 * @package    customfield_sprogramme
 * @copyright  2023 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewrfcs implements renderable, named_templatable {
    /**
     * @var int $status The status to display.
     */
    protected $status;

    /**
     * @var int $page The page number.
     */
    protected $page;

    /**
     * Construct this renderable.
     */
    public function __construct(
        /**
         * @var int $datafieldid.
         */
        private int $datafieldid,
    ) {
    }
    /**
     * Export this data so it can be used in a mustache template.
     *
     * @param renderer_base $output
     * @return array|array[]|stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $this->before_render();

        $data['status'] = $this->get_status_tabs();
        $numrfcs = sprogramme_rfc::count_rfc($this->datafieldid, $this->status);
        $limit = 20;
        $start = $this->page * $limit;
        $url = new moodle_url('/customfield/field/sprogramme/edit.php', $this->get_url_params([]));
        $paging = new paging_bar($numrfcs, $this->page, $limit, $url);
        $data['htmlpagingbar'] = $output->render($paging);


        $rfcs = sprogramme_rfc::get_rfcs($this->datafieldid, $this->status, 0, $start, $limit);

        $data['rfcs'] = [];
        foreach ($rfcs as $rfc) {
            $courseid = utils::get_instanceid_from_datafieldid($rfc->datafieldid);
            $course = get_course($courseid);
            $data['rfcs'][] = [
                'rfcid' => $rfc->id,
                'timemodified' => $rfc->timemodified,
                'userinfo' => $rfc->userinfo,
                'course' => $course,
                'datafieldid' => $rfc->datafieldid,
                'adminid' => $rfc->adminid,
                'type' => $rfc->type,
                'status' => get_string('rfc:' . sprogramme_rfc::CHANGE_TYPES[$rfc->type], 'customfield_sprogramme'),
                'action' => 'showrfc',
            ];

        }
        $data['version'] = time();
        $data['debug'] = $CFG->debugdisplay;

        return $data;
    }

    /**
     * Get the status selector
     * @return array
     */
    public function get_status_tabs(): array {
        $data = [];
        $allowed = [
            sprogramme_rfc::RFC_ACCEPTED => 'accepted',
            sprogramme_rfc::RFC_REJECTED => 'rejected',
        ];
        foreach ($allowed as $key => $status) {
            $data[] = [
                'key' => $key,
                'name' => get_string('rfc:' . $status, 'customfield_sprogramme'),
                'url' => new moodle_url(
                    '/customfield/field/sprogramme/edit.php',
                    $this->get_url_params(['status' => $key])
                ),
                'selected' => $key == $this->status,
            ];
        }
        return $data;
    }

    /**
     * Get the url parameters for this renderable.
     * @param array $params
     * @return array
     */
    public function get_url_params($params): array {
        $defaults = [
            'datafieldid' => $this->datafieldid,
            'pagetype' => 'viewrfcs',
            'status' => $this->status,
            'page' => $this->page,
        ];
        // Params overwrite defaults.
        $params = array_merge($defaults, $params);
        // Remove empty values.
        $params = array_filter($params, function ($value) {
            return !empty($value);
        });
        return $params;
    }

    /**
     * Perform actions before rendering.
     * @return void
     */
    public function before_render(): void {
        $this->status = optional_param('status', sprogramme_rfc::RFC_ACCEPTED, PARAM_INT);
        $this->page = optional_param('page', 0, PARAM_INT);
        $action = optional_param('action', '', PARAM_ALPHANUMEXT);
        switch ($action) {
            case 'deleteall':
                $params = ['action' => $this->status];
                if ($this->datafieldid !== 0) {
                    $params['datafieldid'] = $this->datafieldid;
                }
                $todelete = sprogramme_rfc::get_records($params);
                foreach ($todelete as $rfc) {
                    $rfc->delete();
                }
                break;
            case 'acceptrfc':
                $this->accept_rfc();
                break;
        }


        $delete = optional_param('delete', null, PARAM_INT);
        if ($delete) {
            $todelete = sprogramme_rfc::get_record(['id' => $delete]);
            $todelete->delete();
        }

        $send = optional_param('send', null, PARAM_INT);
        if ($send) {
            $rfc = sprogramme_rfc::get_record(['id' => $send]);
            notifications::send_email($rfc);
        }

        $sendall = optional_param('sendall', null, PARAM_INT);
        if ($sendall) {
            $params = ['datafieldid' => $this->datafieldid];
            if ($this->task) {
                $params['notification'] = $this->task;
            }
            $rfcs = sprogramme_rfc::get_records($params);
            foreach ($rfcs as $rfc) {
                notifications::send_email($rfc);
            }
        }

        $deleteall = optional_param('deleteall', null, PARAM_INT);
        if ($deleteall) {
            $params = ['datafieldid' => $this->datafieldid];
            if ($this->task) {
                $params['notification'] = $this->task;
            }
            $rfcs = sprogramme_rfc::get_records($params);
            foreach ($rfcs as $rfc) {
                $rfc->delete();
            }
        }
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
        return 'customfield_sprogramme/viewrfcs';
    }
}
