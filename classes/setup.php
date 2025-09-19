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
use core_php_time_limit;
use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use xmldb_field;
use xmldb_key;
use xmldb_table;

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
     * @param bool $delete Whether to delete existing records first.
     * @return void
     */
    public static function fill_disclist(bool $delete=true): void {
        global $CFG;

        if ($delete) {
            // Clear the table first.
            $records = sprogramme_disclist::get_records();
            foreach ($records as $record) {
                $record->delete();
            }
        }

        $jsonfile = $CFG->dirroot . '/customfield/field/sprogramme/data/disclist.json';

        if (!file_exists($jsonfile)) {
            throw new \moodle_exception('File not found: ' . $jsonfile);
        }

        $data = json_decode(file_get_contents($jsonfile), true);
        if (empty($data)) {
            throw new \moodle_exception('No data found in: ' . $jsonfile);
        }
        foreach ($data as $item) {
            if (!$delete) {
                $existing = sprogramme_disclist::get_record(['uniqueid' => intval($item['uniqueid'])]);
                if ($existing) {
                    continue;
                }
                $existing->set('type', $item['type']);
                $existing->set('parent', $item['parent'] ?? null);
                $existing->set('name', $item['name']);
                $existing->set('sortorder', $item['sortorder'] ?? 0);
                $existing->save();
                continue;
            }
            $disclist = new sprogramme_disclist();
            $disclist->set('uniqueid', intval($item['uniqueid']) ?? 0);
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

    /**
     * Add datafield and fk
     *
     * @param string $tablename
     * @return void
     */
    public static function add_datafield_id_and_fk(string $tablename) {
        global $DB;
        $dbman = $DB->get_manager();
        // Rename field courseid on table customfield_sprogramme to fieldid.
        $table = new xmldb_table($tablename);
        $field = new xmldb_field('datafieldid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseid');
        // Launch rename field fieldid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table($tablename);
        // Define key fieldid_dk (foreign) to be added to customfield_sprogramme.
        $key = new xmldb_key('datafieldid_fk', XMLDB_KEY_FOREIGN, ['datafieldid'], 'customfield_data', ['id']);
        if (!$dbman->find_key_name($table, $key)) {
            // Launch add key fieldid_dk.
            $dbman->add_key($table, $key);
        } else {
            // Key already exists, but may be not correct, so drop and re-add it.
            $dbman->drop_key($table, $key);
            $dbman->add_key($table, $key);
        }
    }

    /**
     * Drop courseid column
     *
     * @param string $tablename
     * @return void
     */
    public static function drop_courseid_column(string $tablename) {
        global $DB;
        $dbman = $DB->get_manager();
        // Define field courseid to be dropped from customfield_sprogramme.
        $table = new xmldb_table($tablename);
        $field = new xmldb_field('courseid');

        // Conditionally launch drop field courseid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    }
    /**
     * Migrate existing data from courseid to fieldid.
     *
     * @return void
     */
    public static function migrate_courseid_to_datafieldid() {
        global $DB;
        raise_memory_limit(MEMORY_HUGE);
        core_php_time_limit::raise(HOURSECS);
        $currentprogrammefieldid = $DB->get_field('customfield_field', 'id', ['type' => 'sprogramme']);
        if (!$currentprogrammefieldid) {
            $currentprogrammefieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'programme', 'type' => 'text'], IGNORE_MISSING);
        }
        if (!$currentprogrammefieldid) {
            throw new \moodle_exception('No sprogramme field found, cannot migrate data.');
        }
        // Now migrate all data from courseid to fieldid.
        $transaction = $DB->start_delegated_transaction();
        self::update_records('customfield_sprogramme', $currentprogrammefieldid);
        self::update_records('customfield_sprogramme_module', $currentprogrammefieldid);
        self::update_records('customfield_sprogramme_rfc', $currentprogrammefieldid);
        self::update_records('customfield_sprogramme_notification', $currentprogrammefieldid);
        $transaction->allow_commit();
    }

    /**
     * Update records in the given table, changing courseid to fieldid using the provided map.
     *
     * @param string $table The table to update.
     * @param int $currentprogrammefieldid The current programme field.
     */
    private static function update_records(string $table, int $currentprogrammefieldid) {
        global $DB;
        $rs = $DB->get_recordset($table);
        foreach ($rs as $record) {
            $courseid = $record->courseid;
            if (empty($courseid)) {
                continue;
            }
            $context = \context_course::instance($courseid);
            // Check if there is a customfield_data record for this course and field.
            $datarecord = $DB->get_record('customfield_data', ['instanceid' => $courseid, 'fieldid' => $currentprogrammefieldid]);
            $datafieldid = $datarecord->id ?? 0;
            if (!$datafieldid) {
                // Create the data record.
                $datafield = \core_customfield\data_controller::create(0, (object) [
                    'fieldid' => $currentprogrammefieldid,
                    'instanceid' => $courseid,
                    'intvalue' => 1,
                    'contextid' => $context->id,
                ]);
                $datafield->save();
                $datafieldid = $datafield->get('id');
            }
            $record->datafieldid = $datafieldid;
            $DB->update_record($table, $record);
        }
        $rs->close();
    }
}
