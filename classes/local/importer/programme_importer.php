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
            $module = module::get_record(['name' => $modulename]);
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

        return $data;
    }
}
