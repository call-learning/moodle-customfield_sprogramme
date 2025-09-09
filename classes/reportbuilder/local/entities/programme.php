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
use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\report\{column, filter};

/**
 * Class programme
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme extends base {
    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'customfield_sprogramme' => 'programme',
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

    /**
     * Initialise the entity
     *
     * @return base
     */
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
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $programmealias = $this->get_table_alias('customfield_sprogramme');

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
            'courseid',
            new lang_string('programme:courseid', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$programmealias}.courseid")
            ->set_is_sortable(true);
        return $columns;
    }

    /**
     * Return list of all available filters
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
            'courseid',
            new lang_string('programme:courseid', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$programmealias}.courseid"
        ))->add_joins($this->get_joins());

        return $filters;
    }
}
