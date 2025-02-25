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

use customfield_sprogramme\local\api\programme;
use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Class formfield
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class formfield implements renderable, templatable {
    /**
     * Export data for the template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE, $CFG;
        $data = new stdClass();
        $data->courseid = $PAGE->context->instanceid;
        $data->debug = $CFG->debugdisplay;
        $data->disciplines = programme::get_disciplines();
        $data->competences = programme::get_competencies();
        $data->cssurl = new \moodle_url('/customfield/field/sprogramme/scss/styles.css', ['cache' => time()]);
        return $data;
    }
}
