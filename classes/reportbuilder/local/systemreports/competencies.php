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
namespace customfield_sprogramme\reportbuilder\local\systemreports;

use core_reportbuilder\system_report;
use customfield_sprogramme\reportbuilder\local\entities\competency;

/**
 * System report to display competencies
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencies extends system_report {
    #[\Override]
    public function get_default_conditions(): array {
        return [];
    }

    #[\Override]
    protected function initialise(): void {
        $competency = new competency();
        $competencyalias = $competency->get_table_alias('customfield_sprogramme_complist');
        $this->set_main_table('customfield_sprogramme_complist', $competencyalias);
        $this->add_entity($competency);
        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        // Here we do this intentionally as any button inserted in the page results in a javascript error.
        // This is due to fact that if we insert it in an existing form this will nest the form and this is not allowed.
        $isdownloadable = $this->get_parameter('downloadable', true, PARAM_BOOL);
        $hasfilters = $this->get_parameter('hasfilters', true, PARAM_BOOL);
        $this->set_downloadable($isdownloadable);
        $this->set_filter_form_default($hasfilters);
    }

    #[\Override]
    protected function add_columns(): void {
        $columns = [
            'competency:uniqueid',
            'competency:type',
            'competency:parent',
            'competency:name',
            'competency:sortorder',
        ];

        $this->add_columns_from_entities($columns);

        // Default sorting.
        $this->set_initial_sort_column('competency:sortorder', SORT_ASC);
    }

    #[\Override]
    protected function add_filters(): void {
        $filters = [
            'competency:uniqueid',
            'competency:parent',
            'competency:name',
        ];
        $this->add_filters_from_entities($filters);
    }

    #[\Override]
    protected function can_view(): bool {
        return has_capability('moodle/site:config', \context_system::instance());
    }
}
