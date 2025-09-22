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

declare(strict_types=1);

namespace customfield_sprogramme\reportbuilder\local\entities;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\user;
use core_reportbuilder\local\report\{column, filter};
use customfield_sprogramme\reportbuilder\local\helpers\format;
use core_reportbuilder\local\helpers\format as rbformat;
use lang_string;

/**
 * Class rfc
 *
 * Note: we use a temptable to join the data from the customfield_data table
 * with the data from the customfield_sprogramme_rfc table. This is because
 * the customfield_data table can be very large and joining it directly
 * with the customfield_sprogramme_rfc table would be very slow.
 * We create the temptable in the report initialisation.
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Laurent David <laurent@¢all-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfc extends base {

    /**
     * The name of the temporary table used to store RFC data
     */
    const RFC_TEMP_TABLE_NAME = 'temp_reportbuilder_rfcs';

    #[\Override]
    protected function get_default_table_aliases(): array {
        return [
            'rfc' => 'temp_reportbuilder_rfcs',
            'customfield_data' => 'cfdata',
            'customfield_field' => 'cffield',
            'user' => 'user',
            'course' => 'course',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:rfc', 'customfield_sprogramme');
    }

    #[\Override]
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    #[\Override]
    protected function get_all_columns(): array {
        $this->init_temp_table();
        $rfcalias = $this->get_table_alias('rfc');

        $columns[] = (new column(
            'type',
            new lang_string('rfc:type', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$rfcalias}.type")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'cct_ept',
            new lang_string('programme:cct_ept', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.cct_ept, {$rfcalias}.o_cct_ept AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'dd_rse',
            new lang_string('programme:dd_rse', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.dd_rse, {$rfcalias}.o_dd_rse AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'type_ae',
            new lang_string('programme:type_ae', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.type_ae, {$rfcalias}.o_type_ae AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'sequence',
            new lang_string('programme:sequence', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$rfcalias}.sequence, {$rfcalias}.o_sequence AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'intitule_seance',
            new lang_string('programme:intitule_seance', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.intitule_seance, {$rfcalias}.o_intitule_seance AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'cm',
            new lang_string('programme:cm', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.cm, {$rfcalias}.o_cm AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'td',
            new lang_string('programme:td', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.td, {$rfcalias}.o_td AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'tp',
            new lang_string('programme:tp', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.tp, {$rfcalias}.o_tp AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'tpa',
            new lang_string('programme:tpa', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.tpa, {$rfcalias}.o_tpa AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'tc',
            new lang_string('programme:tc', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.tc, {$rfcalias}.o_tc AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'aas',
            new lang_string('programme:aas', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.aas, {$rfcalias}.o_aas AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'fmp',
            new lang_string('programme:fmp', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.fmp, {$rfcalias}.o_fmp AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'perso_av',
            new lang_string('programme:perso_av', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.perso_av, {$rfcalias}.o_perso_av AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'perso_ap',
            new lang_string('programme:perso_ap', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$rfcalias}.perso_ap, {$rfcalias}.o_perso_ap AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'consignes',
            new lang_string('programme:consignes', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.consignes, {$rfcalias}.o_consignes AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'supports',
            new lang_string('programme:supports', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$rfcalias}.supports, {$rfcalias}.o_supports AS oldvalue")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'format_rfc_column']);

        $columns[] = (new column(
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$rfcalias}.timecreated")
            ->set_is_sortable(true)
            ->set_callback([rbformat::class, 'userdate']);

        $columns[] = (new column(
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$rfcalias}.timemodified")
            ->set_is_sortable(true)
            ->set_callback([rbformat::class, 'userdate']);
        return $columns;
    }

    #[\Override]
    protected function get_all_filters(): array {
        $rfcalias = $this->get_table_alias('rfc');

        $filters[] = (new filter(
            text::class,
            'intitule_seance',
            new lang_string('programme:intitule_seance', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.intitule_seance"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'cct_ept',
            new lang_string('programme:cct_ept', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.cct_ept"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'dd_rse',
            new lang_string('programme:dd_rse', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.dd_rse"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'type_ae',
            new lang_string('programme:type_ae', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.type_ae"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'sequence',
            new lang_string('programme:sequence', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.sequence"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'cm',
            new lang_string('programme:cm', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.cm"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'td',
            new lang_string('programme:td', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.td"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tp',
            new lang_string('programme:tp', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.tp"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tpa',
            new lang_string('programme:tpa', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.tpa"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tc',
            new lang_string('programme:tc', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.tc"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'aas',
            new lang_string('programme:aas', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.aas"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'fmp',
            new lang_string('programme:fmp', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.fmp"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'perso_av',
            new lang_string('programme:perso_av', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.perso_av"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'perso_ap',
            new lang_string('programme:perso_ap', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.perso_ap"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'consignes',
            new lang_string('programme:consignes', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.consignes"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'supports',
            new lang_string('programme:supports', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.supports"
        ))->add_joins($this->get_joins());


        $filters[] = (new filter(
            user::class,
            'usermodified',
            new lang_string('programme:usermodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.usermodified"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$rfcalias}.timemodified"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }


    /**
     * Create and fill a temporary table with RFC data
     */
    private function init_temp_table() {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new \xmldb_table(self::RFC_TEMP_TABLE_NAME);
        if ($dbman->table_exists(self::RFC_TEMP_TABLE_NAME)) {
            // If the table already exists, nothing to do.
            return;
        }
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('datafieldid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('modulename', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('modulesortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('uc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cct_ept', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('dd_rse', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('type_ae', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('sequence', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('intitule_seance', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('cm', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('td', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('tp', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('tpa', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('tc', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('aas', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('fmp', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('perso_av', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('perso_ap', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('consignes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('supports', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('o_cct_ept', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('o_dd_rse', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('o_type_ae', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('o_sequence', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('o_intitule_seance', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('o_cm', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_td', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_tp', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_tpa', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_tc', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_aas', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_fmp', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_perso_av', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_perso_ap', XMLDB_TYPE_FLOAT, null, null, null, null, null);
        $table->add_field('o_consignes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('o_supports', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('snapshotsha1', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('adminid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $dbman->create_temp_table($table);

        // Fill the temporary table with RFC data.
        $rfcitems = $DB->get_recordset('customfield_sprogramme_rfc');
        $rfccolumns = [
            'cct_ept',
            'dd_rse',
            'type_ae',
            'sequence',
            'intitule_seance',
            'cm',
            'td',
            'tp',
            'tpa',
            'tc',
            'aas',
            'fmp',
            'perso_av',
            'perso_ap',
            'consignes',
            'supports',
        ];
        foreach ($rfcitems as $rfcitem) {
            $rfcinfos = json_decode($rfcitem->snapshot, true);
            if (!is_array($rfcinfos)) {
                continue;
            }
            $rfcobject = (object)[
                'datafieldid' => $rfcitem->datafieldid,
                'type' => $rfcitem->type,
                'adminid' => $rfcitem->adminid,
                'timecreated' => $rfcitem->timecreated,
                'timemodified' => $rfcitem->timemodified,
                'usermodified' => $rfcitem->usermodified,
            ];

            foreach($rfcinfos as $rfcinfo) {
                $sha1 = sha1($rfcitem->snapshot);
                $rfcobject->snapshotsha1 = $sha1;
                $rfcobject->moduleid = $rfcinfo['moduleid'] ?? null;
                $rfcobject->modulename = $rfcinfo['modulename'] ?? null;
                $rfcobject->modulesortorder = $rfcinfo['modulesortorder'] ?? null;
                $cf = \core_customfield\data_controller::create(intval($rfcitem->datafieldid));
                $rfcobject->uc = $cf->get('instanceid');
                foreach ($rfcinfo['rows'] as $row) {
                    $rfcobject->sortorder = $rfcinfo['moduleid'] ?? null;
                    foreach ($rfccolumns as $column) {
                        foreach ($row['cells'] as $cell) {
                            if ($cell['column'] === $column) {
                                $rfcobject->{$column} = $cell['value'];
                                $rfcobject->{'o_' . $column} = $cell['oldvalue'] ?? null;
                            }
                        }
                    }
                    $DB->insert_record(self::RFC_TEMP_TABLE_NAME, $rfcobject);
                }
            }
        }
        $rfcitems->close();
    }

}
