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
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\filters\number;
use lang_string;
use stdClass;

/**
 * Class discipline assignmen t entity
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class discipline_assignment extends base {
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
        $disciplinealias = $this->get_table_alias('customfield_sprogramme_disc');
        $columns[] = (new column(
            'percentage',
            new lang_string('discipline_assignment:percentage', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$disciplinealias}.percentage")
            ->set_is_sortable(true);

        $disciplineelistalias = $this->get_table_alias('customfield_sprogramme_disclist');
        $columns[] = (new column(
            'percentagewithlabel',
            new lang_string('discipline_assignment:percentage', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins(
                array_merge(
                    $this->get_joins(), [
                         "LEFT JOIN {customfield_sprogramme_disclist} {$disciplineelistalias} ON {$disciplinealias}.did = {$disciplineelistalias}.id"
                    ]
                )
            )
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$disciplinealias}.percentage, {$disciplineelistalias}.name")
            ->set_callback(function($value, stdClass $record) {
                if (!isset($record->name)) {
                    return '';
                }
                return "{$record->name} ({$record->percentage}%)";
            });
        return $columns;
    }

    #[\Override]
    protected function get_all_filters(): array {
        $disciplinealias = $this->get_table_alias('customfield_sprogramme_disc');

        $filters[] = (new filter(
            number::class,
            'percentage',
            new lang_string('discipline_assignment:percentage', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$disciplinealias}.percentage"
        ))->add_joins($this->get_joins());

        return $filters;
    }

    #[\Override]
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:discipline_assignment', 'customfield_sprogramme');
    }

    #[\Override]
    protected function get_default_tables(): array {
        return ['customfield_sprogramme_disc', 'customfield_sprogramme_disclist'];
    }


}