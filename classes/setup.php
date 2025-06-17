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

namespace customfield_sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\persistent\sprogramme_complist;
/**
 * Class setup
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setup {

    /**
     * Fill the table customfield_sprogramme_disclist
     * using a json file.
     * @return void
     */
    public static function fill_disclist(): void {
        global $CFG;

        // Clear the table first.
        $records = sprogramme_disclist::get_records();
        foreach ($records as $record) {
            $record->delete();
        }

        $jsonfile = $CFG->dirroot . '/customfield/field/sprogramme/data/disclist.json';

        if (!file_exists($jsonfile)) {
            throw new \moodle_exception('File not found: ' . $jsonfile);
        }

        $data = json_decode(file_get_contents($jsonfile), true);
        if (empty($data)) {
            throw new \moodle_exception('No data found in: ' . $jsonfile);
        }
        $tempnumbers = [];
        foreach ($data as $item) {
            $disclist = new sprogramme_disclist();
            $disclist->set('uniqueid', Intval($item['uniqueid']) ?? 0);
            $disclist->set('type', $item['type']);
            $disclist->set('parent', $item['parent'] ?? null);
            $disclist->set('name', $item['name']);
            $disclist->set('sortorder', $item['sortorder'] ?? 0);
            $disclist->save();
        }
    }

    /**
     * Fill the table customfield_sprogramme_complist
     * using a json file.
     * @return void
     */
    public static function fill_complist(): void {
        global $CFG;

        // Clear the table first.
        $records = sprogramme_complist::get_records();
        foreach ($records as $record) {
            $record->delete();
        }

        $jsonfile = $CFG->dirroot . '/customfield/field/sprogramme/data/complist.json';
        if (!file_exists($jsonfile)) {
            throw new \moodle_exception('File not found: ' . $jsonfile);
        }
        $data = json_decode(file_get_contents($jsonfile), true);
        if (empty($data)) {
            throw new \moodle_exception('No data found in: ' . $jsonfile);
        }
        foreach ($data as $item) {
            $complist = new sprogramme_complist();
            $complist->set('uniqueid', $item['uniqueid'] ?? 0);
            $complist->set('type', $item['type']);
            $complist->set('parent', $item['parent'] ?? null);
            $complist->set('name', $item['name']);
            $complist->set('sortorder', $item['sortorder'] ?? 0);
            $complist->save();
        }
    }
}
