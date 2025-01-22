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
        $data->disciplines = self::get_disciplines();
        $data->cssurl = new \moodle_url('/customfield/field/sprogramme/scss/styles.css', ['cache' => time()]);
        return $data;
    }

    /**
     * Get all disciplines
     *
     * @return array
     */
    public static function get_disciplines(): array {
        $disciplinesjson = '[
            {"id": 1, "number": 2, "name": "Immunology"},
            {"id": 2, "number": 2, "name": "Literacy & data management"},
            {"id": 3, "number": 2, "name": "Microbiology"},
            {"id": 4, "number": 2, "name": "Parasitology"},
            {"id": 5, "number": 2, "name": "Pathology"},
            {"id": 6, "number": 2, "name": "Pharma-cy-cology-cotherapy"},
            {"id": 7, "number": 2, "name": "Physiology"},
            {"id": 8, "number": 2, "name": "Prof. ethics & communication"},
            {"id": 9, "number": 2, "name": "Toxicology"},
            {"id": 10, "number": 3, "name": "CA_EQ Anesthesiology"},
            {"id": 11, "number": 3, "name": "CA_EQ Clinical pract training"},
            {"id": 12, "number": 3, "name": "CA_EQ Diagnostic imaging"},
            {"id": 13, "number": 3, "name": "CA_EQ Diagnostic pathology"},
            {"id": 14, "number": 3, "name": "CA_EQ Infectious diseases"},
            {"id": 15, "number": 3, "name": "CA_EQ Medecine"},
            {"id": 16, "number": 3, "name": "CA_EQ Preventive medicine"},
            {"id": 17, "number": 3, "name": "CA_EQ Repro & obstetrics"},
            {"id": 18, "number": 3, "name": "CA_EQ Surgery"},
            {"id": 19, "number": 3, "name": "CA_EQ Therapy"},
            {"id": 20, "number": 4, "name": "FPA Anesthesiology"},
            {"id": 21, "number": 4, "name": "FPA Clinical pract training"},
            {"id": 22, "number": 4, "name": "FPA Diagnostic imaging"},
            {"id": 23, "number": 4, "name": "FPA Diagnostic pathology"},
            {"id": 24, "number": 4, "name": "FPA Herd health management"},
            {"id": 25, "number": 4, "name": "FPA Husb, breeding & economics"},
            {"id": 26, "number": 4, "name": "FPA Infectious diseases"},
            {"id": 27, "number": 4, "name": "FPA Medecine"},
            {"id": 28, "number": 4, "name": "FPA Preventive medicine"},
            {"id": 29, "number": 4, "name": "FPA Repro & obstetrics"},
            {"id": 30, "number": 4, "name": "FPA Surgery"},
            {"id": 31, "number": 4, "name": "FPA Therapy"},
            {"id": 32, "number": 5, "name": "Control of food & feed"},
            {"id": 33, "number": 5, "name": "Food hygiene & environ. health"},
            {"id": 34, "number": 5, "name": "Food technology"},
            {"id": 35, "number": 5, "name": "Vet. legis & certification"},
            {"id": 36, "number": 5, "name": "Zoonoses"}
        ]';
        $disciplines = json_decode($disciplinesjson, true);
        return $disciplines;
    }
}
