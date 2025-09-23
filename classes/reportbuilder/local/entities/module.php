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
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\user;
use lang_string;

/**
 * Class module
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module extends base {
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
        $modulealias = $this->get_table_alias('customfield_sprogramme_module');

        $columns[] = (new column(
            'sortorder',
            new lang_string('module:sortorder', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$modulealias}.sortorder")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'name',
            new lang_string('module:name', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$modulealias}.name")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$modulealias}.timecreated")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$modulealias}.timemodified")
            ->set_is_sortable(true);
        return $columns;
    }

    #[\Override]
    protected function get_all_filters(): array {
        $modulealias = $this->get_table_alias('customfield_sprogramme_module');

        $filters[] = (new filter(
            number::class,
            'sortorder',
            new lang_string('module:sortorder', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$modulealias}.sortorder"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'name',
            new lang_string('module:name', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$modulealias}.name"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            user::class,
            'usermodified',
            new lang_string('programme:usermodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$modulealias}.usermodified"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('programme:timecreated', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$modulealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('programme:timemodified', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$modulealias}.timemodified"
        ))
            ->add_joins($this->get_joins());
        return $filters;
    }

    #[\Override]
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:module', 'customfield_sprogramme');
    }

    #[\Override]
    protected function get_default_tables(): array {
        return ['customfield_sprogramme_module'];
    }
}
