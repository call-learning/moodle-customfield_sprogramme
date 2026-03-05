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

namespace customfield_sprogramme\local\form;

use context;
use context_course;
use context_user;
use core_form\dynamic_form;
use core_text;
use csv_import_reader;
use customfield_sprogramme\local\importer\programme_importer;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\persistent\sprogramme_visa;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\local\visa_manager;
use customfield_sprogramme\utils;
use moodle_exception;
use moodle_url;

/**
 * Class planning_upload_form
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visa_validate_form extends dynamic_form {
    /**
     * Process the form submission
     *
     * @return array
     * @throws moodle_exception
     */
    public function process_dynamic_submission(): array {
        $data = $this->get_data();
        return [
            'result' => true,
            'statuscode' => $data->status == sprogramme_visa::STATUS_APPROVED ? 'approved' : 'rejected',
            'comment' => $data->comment,
        ];
    }

    /**
     * Get context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $rfcid = $this->optional_param('rfcid', null, PARAM_INT);
        $rfc = sprogramme_rfc::get_record(['id' => $rfcid]);
        return $rfc->get_context();
    }

    /**
     * TODO, find a better capability
     *
     * @return void
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!has_capability('moodle/course:update', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('invalidaccess');
        }
    }

    /**
     * Get page URL
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $context = $this->get_context_for_dynamic_submission();
        return new moodle_url('/course/edit.php', ['id' => $context->instanceid]);
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $rfcid = $this->optional_param('rfcid', null, PARAM_INT);
        $mform->addElement('hidden', 'rfcid', $rfcid);
        $mform->setType('rfcid', PARAM_INT);
        $mform->addElement('textarea', 'comment', get_string('comment', 'customfield_sprogramme'));
        $mform->setType('comment', PARAM_TEXT);
        // Add radio buttons for accept/reject.
        $mform->addElement(
            'radio',
            'status',
            '',
            get_string('accept', 'customfield_sprogramme'),
            sprogramme_visa::STATUS_APPROVED
        );
        $mform->addElement(
            'radio',
            'status',
            '',
            get_string('reject', 'customfield_sprogramme'),
            sprogramme_visa::STATUS_REJECTED
        );
        $mform->setType('status', PARAM_INT);
    }

    /**
     * Set data for dynamic submission
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        global $USER;
        $rfcid = $this->optional_param('rfcid', 0, PARAM_INT);
        $defaultstatus = sprogramme_visa::STATUS_APPROVED;
        $defaultcomment = '';
        $visa = sprogramme_visa::get_record(['rfcid' => $rfcid, 'visauser' => $USER->id]);
        if (!empty($visa)) {
            $defaultstatus = intval($visa->get('status'));
            $defaultcomment = $visa->get('comment');
        }
        $data = [
            'rfcid' => $rfcid,
            'status' => $defaultstatus,
            'comment' => $defaultcomment,
        ];
        parent::set_data((object) $data);
    }
}
