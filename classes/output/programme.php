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

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Renderable for programme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 CALL Learning <Laurent David>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme implements renderable, templatable {

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
        $data->cssurl = new \moodle_url('/customfield/field/sprogramme/scss/styles.css', ['cache' => time()]);
        return $data;
    }
}