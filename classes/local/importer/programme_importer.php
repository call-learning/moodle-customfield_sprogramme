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

namespace customfield_sprogramme\local\importer;

use customfield_sprogramme\local\persistent\sprogramme as programme;
use customfield_sprogramme\local\persistent\sprogramme_module as module;
use customfield_sprogramme\local\api\programme as programme_api;
/**
 * Class programme_importer
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme_importer extends base_persistent_importer {

    /**
     * @var array $modulecache Cache for the module id.
     */
    protected $modulecache = [];

    /**
     * @var int $sortorder The sort order for the module.
     */
    protected $sortorder;

    /**
     * Get row content from persistent data
     *
     * This can be used to tweak the data before it is persisted and maybe get some external keys.
     *
     * @param array $row
     * @param csv_iterator $reader
     * @return object
     */
    protected function to_persistent_data(array $row, csv_iterator $reader): object {
        $courseid = $this->options['courseid'];
        $data = parent::to_persistent_data($row, $reader);
        $modulename = $data->module;
        if (!isset($this->modulecache[$modulename])) {
            $module = module::get_record(['name' => $modulename, 'courseid' => $courseid]);
            $sortorder = module::count_records() + 1;
            if (!$module) {
                $module = new module(null, (object) [
                    'name' => $modulename,
                    'courseid' => $courseid,
                    'sortorder' => $sortorder,
                ]);
                $module->save();
            }
            $this->modulecache[$modulename] = $module->get('id');
        }
        $data->uc = $courseid;
        $data->courseid = $courseid;
        $data->moduleid = $this->modulecache[$modulename];
        $data->sortorder = $this->sortorder++;
        $programproperties = programme::get_properties();
        foreach ($programproperties as $property => $definition) {
            if ($definition['type'] === PARAM_INT) {
                if ($data->$property === '') {
                    $data->$property = null;
                } else {
                    $data->$property = (int) $data->$property;
                }
            }
            if ($definition['type'] === PARAM_FLOAT) {
                if ($data->$property === '') {
                    $data->$property = null;
                } else {
                    $data->$property = (float) $data->$property;
                }
            }
        }
        // Turn competencies and disciplines persistent data.
        $data->competencies = $this->get_list($data->competencies, 'competencies');
        $data->disciplines = $this->get_list($data->disciplines, 'disciplines');

        return $data;
    }

    /**
     * Turn competencies and disciplines persistent data.
     * @param string $data
     * @param string $type
     * @return array
     */
    protected function get_list(string $data, string $type): array {
        // Split the data by |
        $data = explode('|', $data);
        // Get the percentage (50%) from the data.
        $data = array_map(function ($item) {
            $item = explode('(', $item);
            $item[1] = str_replace(['(', ')', '%'], '', $item[1]);
            return [
                'name' => trim($item[0]),
                'percentage' => (float) $item[1],
            ];
        }, $data);

        if ($type == 'disciplines') {
            $disciplines = programme_api::get_disciplines();
            foreach ($data as $key => $item) {
                $matchingdiscipline = array_filter($disciplines, function ($discipline) use ($item) {
                    return $discipline['name'] == $item['name'];
                });
                if (empty($matchingdiscipline)) {
                    unset($data[$key]);
                } else {
                    $item['did'] = array_values($matchingdiscipline)[0]['id'];
                    $data[$key] = $item;
                }
            }
        }
        // Same as above we need the cid
        if ($type == 'competencies') {
            $competencies = programme_api::get_competencies();
            foreach ($data as $key => $item) {
                $matchingcompetency = array_filter($competencies, function ($competency) use ($item) {
                    return $competency['name'] == $item['name'];
                });
                if (empty($matchingcompetency)) {
                    unset($data[$key]);
                } else {
                    $item['cid'] = array_values($matchingcompetency)[0]['id'];
                    $data[$key] = $item;
                }
            }
        }
        return $data;
    }
}
