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
use customfield_sprogramme\local\api\programme as programme_api;
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
     * @var int $courseid.
     */
    private $courseid;

    /**
     * Construct this renderable.
     *
     * @param int $courseid The course id.
     */
    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Export data for the template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;
        $modules = programme_api::get_data($this->courseid);
        $columns = programme_api::get_column_structure($this->courseid);
        $data = [
            'modulesstatic' => $modules,
            'columns' => $columns,
        ];
        return $data;
    }
}
