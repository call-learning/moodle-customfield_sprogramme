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

use core\exception\moodle_exception;
use customfield_sprogramme\local\persistent\sprogramme as programme;
use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\persistent\sprogramme_module as module;

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
     * Persistent class
     *
     * @param array|null $options options like unique for the list of fields used to check for existing records
     * @throws moodle_exception
     */
    public function __construct(?array $options = []) {
        parent::__construct(programme::class, $options);
    }

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
        $datafieldid = $this->options['datafieldid'];
        $data = parent::to_persistent_data($row, $reader);
        $modulename = $data->module;
        if (!isset($this->modulecache[$modulename])) {
            $module = module::get_record(['name' => $modulename, 'datafieldid' => $datafieldid]);
            $sortorder = module::count_records() + 1;
            if (!$module) {
                $module = new module(null, (object) [
                    'name' => $modulename,
                    'datafieldid' => $datafieldid,
                    'sortorder' => $sortorder,
                ]);
                $module->save();
            }
            $this->modulecache[$modulename] = $module->get('id');
        }
        $data->uc = $datafieldid;
        $data->datafieldid = $datafieldid;
        $data->moduleid = $this->modulecache[$modulename];
        $data->sortorder = $this->sortorder++;
        $programproperties = programme::get_properties();
        foreach ($programproperties as $property => $definition) {
            if ($definition['type'] === PARAM_INT) {
                if (!isset($data->$property)) {
                    continue;
                }
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
        $disciplines = [];
        for ($i = 1; $i <= 3; $i++) {
            $disciplinekey = 'disciplines' . $i;
            $percentagekey = '%_disciplines' . $i;
            if (isset($data->$disciplinekey) && !empty($data->$disciplinekey)) {
                $disciplines[] = [
                    'name' => $data->$disciplinekey,
                    'percentage' => isset($data->$percentagekey) ? (float) $data->$percentagekey : 0,
                ];
            }
        }
        // If the all the percentages are 0, set the percentage to 100 / count($disciplines).
        if (count($disciplines) && array_sum(array_column($disciplines, 'percentage')) == 0) {
            $percentage = floor(100 / count($disciplines));
            foreach ($disciplines as $key => $discipline) {
                $disciplines[$key]['percentage'] = $percentage;
            }
        }
        if (count($disciplines)) {
            $data->disciplines = $this->get_list($disciplines, 'disciplines');
        }

        $competencies = [];
        for ($i = 1; $i <= 4; $i++) {
            $competencykey = 'competencies' . $i;
            $percentagekey = '%_competencies' . $i;
            if (isset($data->$competencykey) && !empty($data->$competencykey)) {
                $competencies[] = [
                    'name' => $data->$competencykey,
                    'percentage' => isset($data->$percentagekey) ? (float) $data->$percentagekey : 0,
                ];
            }
        }
        // If the all the percentages are 0, set the percentage to 100 / count($competencies).
        if (count($competencies) && array_sum(array_column($competencies, 'percentage')) == 0) {
            $percentage = floor(100 / count($competencies));

            foreach ($competencies as $key => $competency) {
                $competencies[$key]['percentage'] = $percentage;
            }
        }
        if (count($competencies)) {
            $data->competencies = $this->get_list($competencies, 'competencies');
        }
        return $data;
    }

    /**
     * Turn competencies and disciplines persistent data.
     * @param array $data
     * @param string $type
     * @return array
     */
    protected function get_list(array $data, string $type): array {
        if ($type == 'disciplines') {
            $disciplines = sprogramme_disclist::get_sorted();
            $disciplines = $this->flattern_list($disciplines);
            $disciplinewithid = array_column($disciplines, 'uniqueid', 'name');

            foreach ($data as $key => $item) {
                $itemname = trim($item['name']);
                if (isset($disciplinewithid[$itemname])) {
                    $item['did'] = $disciplinewithid[$itemname];
                    $data[$key] = $item;
                } else {
                    unset($data[$key]);
                }
            }
        }
        if ($type == 'competencies') {
            $competencies = sprogramme_complist::get_sorted();
            $competencies = $this->flattern_list($competencies);
            $competencieswithid = array_column($competencies, 'uniqueid', 'name');
            foreach ($data as $key => $item) {
                $itemname = trim($item['name']);
                if (isset($competencieswithid[$itemname])) {
                    $item['cid'] = $competencieswithid[$itemname];
                    $data[$key] = $item;
                } else {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * Flatten a list of items with nested items.
     *
     * @param array $list
     * @return array
     */
    private function flattern_list(array $list): array {
        $flatterned = [];
        foreach ($list as $item) {
            if (isset($item['items']) && is_array($item['items'])) {
                $flatterned = array_merge($flatterned, $this->flattern_list($item['items']));
            } else {
                $flatterned[] = $item;
            }
        }
        return $flatterned;
    }
}
