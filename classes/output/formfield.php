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

use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\programme_manager;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class formfield
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class formfield implements renderable, templatable {
    /**
     * Construct this renderable.
     *
     * @param int $datafieldid The datafield id.
     */
    public function __construct(
        /**
         * @var int $datafieldid.
         */
        private int $datafieldid
    ) {

    }
    /**
     * Export data for the template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE, $CFG;
        $data = new stdClass();
        $data->datafieldid = $this->datafieldid; // New field.
        $data->debug = $CFG->debugdisplay;
        $programmemanager = new programme_manager($this->datafieldid);
        $data->disciplines = sprogramme_disclist::get_sorted();
        $data->competences = sprogramme_complist::get_sorted();
        $data->canedit = has_capability('customfield/sprogramme:edit', $PAGE->context);
        $data->editrfcs = has_capability('customfield/sprogramme:editall', $PAGE->context);

        $data->rfcsurl =
            new \moodle_url('/customfield/field/sprogramme/edit.php',
                [
                    'datafieldid' => $data->datafieldid,
                    'pagetype' => 'viewrfcs',
                ]
            );
        $data->hashistory = $programmemanager->has_history() ? 1 : 0;
        return $data;
    }
}
