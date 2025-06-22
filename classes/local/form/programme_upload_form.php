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
use customfield_sprogramme\local\api\programme;
use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\importer\programme_importer;
use moodle_exception;
use moodle_url;

/**
 * Class planning_upload_form
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme_upload_form extends dynamic_form {
    /**
     * Process the form submission
     *
     * @return array
     * @throws moodle_exception
     */
    public function process_dynamic_submission(): array {
        global $USER;
        $context = $this->get_context_for_dynamic_submission();
        $data = $this->get_data();
        // Get the file and create the content based on it.
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $this->get_data()->csvfile, 'itemid, filepath,
            filename', false);
        if (!empty($files)) {
            $file = reset($files);
            $filepath = make_request_directory() . '/' . $file->get_filename();
            $file->copy_content_to($filepath);
            try {
                programme::delete_programme($data->courseid);
                $programimporter = new programme_importer(['courseid' => $data->courseid]);
                $programimporter->import($filepath, "comma");
            } finally {
                unlink($filepath);
            }
        }
        return [
            'result' => true,
            'returnurl' => new moodle_url('/course/edit.php', ['id' => $data->courseid]),
        ];
    }

    /**
     * Get context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        $context = context_course::instance($courseid);
        return $context;
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
        $cmid = $this->optional_param('cmid', null, PARAM_INT);
        return new moodle_url('/mod/competvet/view.php', ['pagetype' => 'manageplanning', 'id' => $cmid, 'return' => true]);
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        // Upload the CSV file.
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'mod_data'), null, [
            'maxbytes' => 0,
            'accepted_types' => ['.csv'],
        ]);
    }

    /**
     * Set data for dynamic submission
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $data = [
            'courseid' => $this->optional_param('courseid', 0, PARAM_INT),
        ];
        parent::set_data((object) $data);
    }
}
