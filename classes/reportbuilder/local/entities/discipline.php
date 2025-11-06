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

use core_collator;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\autocomplete;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\report\{column, filter};
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use lang_string;

/**
 * Class discipline entity
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class discipline extends base {
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
        $competencyalias = $this->get_table_alias('customfield_sprogramme_disclist');
        $columns[] = (new column(
            'uniqueid',
            new lang_string('discipline:uniqueid', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$competencyalias}.uniqueid")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'type',
            new lang_string('discipline:type', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$competencyalias}.type")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'parent',
            new lang_string('discipline:parent', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$competencyalias}.parent")
            ->set_is_sortable(true)
            ->add_callback(static function (string $value): string {
                $record = sprogramme_disclist::get_record(['id' => $value]);
                return $record ? $record->get('name') : '';
            });

        $columns[] = (new column(
            'name',
            new lang_string('discipline:name', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$competencyalias}.name")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'sortorder',
            new lang_string('discipline:sortorder', 'customfield_sprogramme'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$competencyalias}.sortorder")
            ->set_is_sortable(true);
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
        $competencyalias = $this->get_table_alias('customfield_sprogramme_disclist');

        $filters[] = (new filter(
            text::class,
            'uniqueid',
            new lang_string('discipline:uniqueid', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$competencyalias}.uniqueid"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            autocomplete::class,
            'parent',
            new lang_string('discipline:parent', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$competencyalias}.parent"
        ))->add_joins($this->get_joins())
            ->set_options_callback(static function (): array {
                $options = sprogramme_disclist::get_records(['type' => 'item'], 'name');
                $options = array_map(function ($option) {
                    return $option->get('name');
                }, $options);
                core_collator::asort($options);
                return $options;
            });

        $filters[] = (new filter(
            text::class,
            'name',
            new lang_string('discipline:name', 'customfield_sprogramme'),
            $this->get_entity_name(),
            "{$competencyalias}.name"
        ))->add_joins($this->get_joins());

        return $filters;
    }

    #[\Override]
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:discipline', 'customfield_sprogramme');
    }

    #[\Override]
    protected function get_default_tables(): array {
        return ['customfield_sprogramme_disclist'];
    }
}
