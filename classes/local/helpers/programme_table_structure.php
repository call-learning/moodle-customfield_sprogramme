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

namespace customfield_sprogramme\local\helpers;

/**
 * Class programme_table_structure
 *
 * @package    customfield_sprogramme
 * @copyright  2024 CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class programme_table_structure {
    /**
     * Get the structure of the sprogramme table
     *
     * @return array
     */
    public static function get(): array {
        return [
            [
                'column' => 'dd_rse',
                'type' => 'select',
                'select' => true,
                'visible' => false,
                'canedit' => true,
                'label' => get_string('programme:dd_rse', 'customfield_sprogramme'),
                'help' => get_string('dd_rse_help', 'customfield_sprogramme'),
                'columnid' => 7,
                'length' => 20,
                'field' => 'select',
                'sample_value' => '',
                'options' => [
                    [
                        'name' => 'à remplir',
                        'selected' => false,
                    ],
                    [
                        'name' => 'Sans lien avec DD/RSE',
                        'selected' => false,
                    ],
                    [
                        'name' => 'DD/RSE',
                        'selected' => false,
                    ],
                ],
                'group' => '',
            ],
            [
                'column' => 'intitule_seance',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => get_string('programme:intitule_seance', 'customfield_sprogramme'),
                'help' => get_string('intitule_seance_help', 'customfield_sprogramme'),
                'columnid' => 19,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
                'group' => '',
            ],
            [
                'column' => 'cm',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:cm', 'customfield_sprogramme'),
                'help' => get_string('cm_help', 'customfield_sprogramme'),
                'columnid' => 20,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'td',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:td', 'customfield_sprogramme'),
                'help' => get_string('td_help', 'customfield_sprogramme'),
                'columnid' => 21,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'tp',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:tp', 'customfield_sprogramme'),
                'help' => get_string('tp_help', 'customfield_sprogramme'),
                'columnid' => 22,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'tpa',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:tpa', 'customfield_sprogramme'),
                'help' => get_string('tpa_help', 'customfield_sprogramme'),
                'columnid' => 23,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'tc',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:tc', 'customfield_sprogramme'),
                'help' => get_string('tc_help', 'customfield_sprogramme'),
                'columnid' => 24,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'aas',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:aas', 'customfield_sprogramme'),
                'help' => get_string('aas_help', 'customfield_sprogramme'),
                'columnid' => 25,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'fmp',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:fmp', 'customfield_sprogramme'),
                'help' => get_string('fmp_help', 'customfield_sprogramme'),
                'columnid' => 26,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => 'unique',
                'sum' => 0,
            ],
            [
                'column' => 'perso_av',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:perso_av', 'customfield_sprogramme'),
                'help' => get_string('perso_av_help', 'customfield_sprogramme'),
                'columnid' => 27,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'group' => '',
                'sum' => 0,
            ],
            [
                'column' => 'perso_ap',
                'type' => PARAM_FLOAT,
                'float' => true,
                'visible' => true,
                'canedit' => false,
                'label' => get_string('programme:perso_ap', 'customfield_sprogramme'),
                'help' => get_string('perso_ap_help', 'customfield_sprogramme'),
                'columnid' => 28,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'min' => 0,
                'max' => 99,
                'group' => '',
                'sum' => 0,
            ],
            [
                'column' => 'consignes',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => get_string('programme:consignes', 'customfield_sprogramme'),
                'help' => get_string('consignes_help', 'customfield_sprogramme'),
                'columnid' => 29,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
                'group' => '',
            ],
            [
                'column' => 'supports',
                'type' => PARAM_TEXT,
                'text' => true,
                'visible' => true,
                'canedit' => true,
                'label' => get_string('programme:supports', 'customfield_sprogramme'),
                'help' => get_string('supports_help', 'customfield_sprogramme'),
                'columnid' => 30,
                'length' => 3000,
                'field' => 'text',
                'sample_value' => '...',
                'group' => '',
            ],
        ];
    }
}
