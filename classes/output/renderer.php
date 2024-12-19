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

use plugin_renderer_base;
use renderable;

/**
 * Class data
 *
 * @package     customfield_sprogramme
 * @copyright   2024 CALL Learning <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Render the output
     *
     * @param programme $programme
     * @return string
     */
    public function render(renderable $programme) {
        global $CFG;
        $data = $programme->export_for_template($this);
        $data->debug = $CFG->debugdisplay;
        return $this->render_from_template('customfield_sprogramme/programme', $data);
    }
}