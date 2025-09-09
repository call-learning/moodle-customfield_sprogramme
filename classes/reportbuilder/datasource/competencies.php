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

namespace customfield_sprogramme\reportbuilder\datasource;

use core_reportbuilder\datasource;
use customfield_sprogramme\reportbuilder\local\entities\competency;

/**
 * Datasource for competencies
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencies extends datasource {
    #[\Override]
    public static function get_name(): string {
        return get_string('report:competency', 'customfield_sprogramme');
    }

    #[\Override]
    public function get_default_columns(): array {
        return [
            'competency:uniqueid',
            'competency:type',
            'competency:parent',
            'competency:name',
            'competency:sortorder',
        ];
    }

    #[\Override]
    public function get_default_filters(): array {
        return [
            'competency:uniqueid',
            'competency:parent',
            'competency:name',
        ];
    }

    #[\Override]
    protected function initialise(): void {
        $competency = new competency();
        $competencyalias = $competency->get_table_alias('customfield_sprogramme_complist');
        $this->set_main_table('customfield_sprogramme_complist', $competencyalias);
        $this->add_entity($competency);
        $this->add_all_from_entities();
    }

    #[\Override]
    public function get_default_conditions(): array {
        return [];
    }
}
