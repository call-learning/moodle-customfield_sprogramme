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

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_customfield_sprogramme_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024121700) {

        // Define field courseid to be added to customfield_sprogramme.
        $table = new xmldb_table('customfield_sprogramme');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseid');

        // Conditionally launch add field sortorder.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'supports');

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2024121700, 'customfield', 'sprogramme');
    }

    if ($oldversion < 2025011000) {

        // Define field did to be added to customfield_sprogramme_disc.
        $table = new xmldb_table('customfield_sprogramme_disc');
        $field = new xmldb_field('did', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'pid');

        // Conditionally launch add field did.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'percentage');

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025011000, 'customfield', 'sprogramme');

    }

    if ($oldversion < 2025011500) {

        // Changing precision of field cct_ept on table customfield_sprogramme to (254).
        $table = new xmldb_table('customfield_sprogramme');
        $field = new xmldb_field('cct_ept', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'uc');

        // Launch change of precision for field cct_ept.
        $dbman->change_field_precision($table, $field);

        $field = new xmldb_field('dd_rse', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'cct_ept');

        // Launch change of precision for field dd_rse.
        $dbman->change_field_precision($table, $field);

        $field = new xmldb_field('type_ae', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'dd_rse');

        // Launch change of precision for field type_ae.
        $dbman->change_field_precision($table, $field);

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025011500, 'customfield', 'sprogramme');
    }

    if ($oldversion < 2025011600) {

        // Define table customfield_sprogramme_module to be created.
        $table = new xmldb_table('customfield_sprogramme_module');

        // Adding fields to table customfield_sprogramme_module.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table customfield_sprogramme_module.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for customfield_sprogramme_module.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025011600, 'customfield', 'sprogramme');
    }

    if ($oldversion < 2025011601) {

        // Define field moduleid to be added to customfield_sprogramme.
        $table = new xmldb_table('customfield_sprogramme');
        $field = new xmldb_field('moduleid', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'courseid');

        // Conditionally launch add field moduleid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025011601, 'customfield', 'sprogramme');
    }

    if ($oldversion < 2025021200) {

        // Define table customfield_sprogramme_competencies to be created.
        $table = new xmldb_table('customfield_sprogramme_competencies');

        // Adding fields to table customfield_sprogramme_competencies.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('pid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('competency', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('percentage', XMLDB_TYPE_FLOAT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table customfield_sprogramme_competencies.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_programme', XMLDB_KEY_FOREIGN, ['pid'], 'customfield_sprogramme', ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for customfield_sprogramme_competencies.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025021200, 'customfield', 'sprogramme');
    }

    if ($oldversion < 2025021900) {

        // Changing type of field intitule_seance on table customfield_sprogramme to text.
        $table = new xmldb_table('customfield_sprogramme');
        $field = new xmldb_field('intitule_seance', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sequence');

        // Launch change of type for field intitule_seance.
        $dbman->change_field_type($table, $field);

        // Sprogramme savepoint reached.
        upgrade_plugin_savepoint(true, 2025021900, 'customfield', 'sprogramme');
    }

    return true;
}
