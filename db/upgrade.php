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

/**
 * Upgrade steps for Programme customfield
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    customfield_sprogramme
 * @category   upgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use customfield_sprogramme\setup;

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_customfield_sprogramme_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025091100) {
        setup::add_datafield_id_and_fk('customfield_sprogramme');
        setup::add_datafield_id_and_fk('customfield_sprogramme_module');
        setup::add_datafield_id_and_fk('customfield_sprogramme_rfc');
        setup::add_datafield_id_and_fk('customfield_sprogramme_notification');
        // Now we need to migrate all data from courseid to fieldid.
        // We will do this in a scheduled task to avoid timeouts.
        setup::migrate_courseid_to_datafieldid();
        upgrade_plugin_savepoint(true, 2025091100, 'customfield', 'sprogramme');
    }
    if ($oldversion < 2025091300) {
        // We no longer need the courseid column, drop it.
        setup::drop_courseid_column('customfield_sprogramme');
        setup::drop_courseid_column('customfield_sprogramme_module');
        setup::drop_courseid_column('customfield_sprogramme_rfc');
        setup::drop_courseid_column('customfield_sprogramme_notification');

        // We also need to drop the competency field as we now use a separate table for that.
        $dbman = $DB->get_manager();
        $table = new xmldb_table('customfield_sprogramme_competencies');
        $field = new xmldb_field('competency');

        // Conditionally launch drop field competency.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $table = new xmldb_table('customfield_sprogramme_disc');
        $field = new xmldb_field('discipline');

        // Conditionally launch drop field discipline.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025091300, 'customfield', 'sprogramme');
    }
    if ($oldversion < 2025091301) {
        // We need to update the disclist and complist tables.
        setup::fill_disclist();
        upgrade_plugin_savepoint(true, 2025091301, 'customfield', 'sprogramme');
    }

    return true;
}
