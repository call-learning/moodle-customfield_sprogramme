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
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use customfield_sprogramme\reportbuilder\local\entities\module;
use customfield_sprogramme\reportbuilder\local\entities\rfc;
use customfield_sprogramme\reportbuilder\local\entities\rfc_totals;

/**
 * RFCs datasource
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rfcs_totals extends datasource {
    #[\Override]
    public static function get_name(): string {
        return get_string('report:rfc', 'customfield_sprogramme');
    }
    #[\Override]
    public function get_default_columns(): array {
        return [
            'validator:fullnamewithlink',
            'usercreated:fullnamewithlink',
            'rfc:timecreated',
            'rfc:timemodified',
            'course:coursefullnamewithlink',
            'rfc_totals:cm',
            'rfc_totals:td',
            'rfc_totals:tp',
            'rfc_totals:tpa',
            'rfc_totals:tc',
            'rfc_totals:aas',
            'rfc_totals:fmp',
        ];
    }

    #[\Override]
    public function get_default_filters(): array {
        return [
            'rfc_totals:cm',
            'rfc_totals:td',
            'rfc_totals:tp',
            'rfc_totals:tpa',
            'rfc_totals:tc',
            'rfc_totals:aas',
            'rfc_totals:fmp',
        ];
    }
    #[\Override]
    public function get_default_conditions(): array {
        return [];
    }

    #[\Override]
    protected function initialise(): void {
        $rfc = new rfc_totals();

        $rfcalias = $rfc->get_table_alias('rfc_totals');
        $this->set_main_table(rfc::RFC_TEMP_TABLE_NAME, $rfcalias);
        $this->add_entity($rfc);

        // Join the course entity to the badge entity, coalescing courseid with the siteid for site badges.
        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $this->add_entity($courseentity
            ->add_join("LEFT JOIN {course} {$coursealias}
                ON {$coursealias}.id = {$rfcalias}.uc"));

        $userentity = new user();
        $userentity->set_entity_name('usercreated');
        $userentity->set_entity_title(new \lang_string('usercreated'));
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$rfcalias}.usercreated"));

        $validatorentity = new user();
        $validatorentity->set_entity_name('validator');
        $validatorentity->set_entity_title(new \lang_string('validator', 'customfield_sprogramme'));
        $validatoralias = $validatorentity->get_table_alias('user');
        $this->add_entity($validatorentity
            ->add_join("LEFT JOIN {user} {$validatoralias} ON {$validatoralias}.id = {$rfcalias}.adminid"));

        $this->add_all_from_entities();
    }
}
