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
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\filters\user;
use core_reportbuilder\local\helpers\format;
use lang_string;

/**
 * Class programme
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme extends base {
    #[\Override]
    protected function get_default_table_aliases(): array {
        return [
            'customfield_sprogramme' => 'programme',
            'customfield_data' => 'cfdata',
            'customfield_field' => 'cffield',
            'user' => 'user',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:programme', 'customfield_sprogramme');
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

    /**
     * Returns list of all available columns.
     *
     * These are all the columns available to use in any report that uses this entity.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $programmealias = $this->get_table_alias('customfield_sprogramme');

        $columns[] = (new column(
            'sortorder',
            new lang_string('programme:sortorder', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$programmealias}.sortorder")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'cct_ept',
            new lang_string('programme:cct_ept', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.cct_ept")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'dd_rse',
            new lang_string('programme:dd_rse', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.dd_rse")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'type_ae',
            new lang_string('programme:type_ae', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.type_ae")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'sequence',
            new lang_string('programme:sequence', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$programmealias}.sequence")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'intitule_seance',
            new lang_string('programme:intitule_seance', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.intitule_seance")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'cm',
            new lang_string('programme:cm', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.cm")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'td',
            new lang_string('programme:td', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.td")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'tp',
            new lang_string('programme:tp', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.tp")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'tpa',
            new lang_string('programme:tpa', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.tpa")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'tc',
            new lang_string('programme:tc', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.tc")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'aas',
            new lang_string('programme:aas', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.aas")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'fmp',
            new lang_string('programme:fmp', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.fmp")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'perso_av',
            new lang_string('programme:perso_av', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.perso_av")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'perso_ap',
            new lang_string('programme:perso_ap', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_fields("{$programmealias}.perso_ap")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'consignes',
            new lang_string('programme:consignes', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.consignes")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'supports',
            new lang_string('programme:supports', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.supports")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$programmealias}.timecreated")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$programmealias}.timemodified")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);
        return $columns;
    }

    /**
     * Returns list of all available filters.
     *
     * These are all the filters available to use in any report that uses this entity.
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $programmealias = $this->get_table_alias('customfield_sprogramme');

        $filters[] = (new filter(
            text::class,
            'intitule_seance',
            new lang_string('programme:intitule_seance', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.intitule_seance"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'cct_ept',
            new lang_string('programme:cct_ept', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.cct_ept"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'dd_rse',
            new lang_string('programme:dd_rse', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.dd_rse"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'type_ae',
            new lang_string('programme:type_ae', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.type_ae"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'sequence',
            new lang_string('programme:sequence', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.sequence"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'cm',
            new lang_string('programme:cm', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.cm"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'td',
            new lang_string('programme:td', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.td"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tp',
            new lang_string('programme:tp', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.tp"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tpa',
            new lang_string('programme:tpa', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.tpa"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'tc',
            new lang_string('programme:tc', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.tc"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'aas',
            new lang_string('programme:aas', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.aas"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'fmp',
            new lang_string('programme:fmp', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.fmp"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'perso_av',
            new lang_string('programme:perso_av', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.perso_av"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'perso_ap',
            new lang_string('programme:perso_ap', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.perso_ap"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'consignes',
            new lang_string('programme:consignes', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.consignes"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'supports',
            new lang_string('programme:supports', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.supports"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            user::class,
            'usermodified',
            new lang_string('programme:usermodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.usermodified"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.timemodified"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
