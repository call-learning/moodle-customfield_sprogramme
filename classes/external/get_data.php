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

namespace customfield_sprogramme\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;

use customfield_sprogramme\local\api\programme;

/**
 * Class get_data
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_data extends external_api {

        /**
         * Get the table structure for the custom field
         * @return array $table
         */
    public static function get_table_structure(): array {
        $columns = [
            '0' => [
                'column' => 'cct_ept',
                'type' => 'select',
                'label' => 'CCT / EPT',
                'columnid' => 6,
                'length' => 20,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
                'options' => [
                    [
                        'name' => 'Autre',
                        'selected' => false,
                    ],
                    [
                        'name' => 'CCT',
                        'selected' => false,
                    ],
                    [
                        'name' => 'EPT',
                        'selected' => false,
                    ],
                ],
            ],
            '1' => [
                'column' => 'dd_rse',
                'type' => 'select',
                'label' => 'DD / RSE',
                'columnid' => 7,
                'length' => 20,
                'field' => 'float',
                'sample_value' => '',
                'active' => true,
                'options' => [
                    [
                        'name' => 'Autre',
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
            ],
            '2' => [
                'column' => 'type_ae',
                'type' => 'select',
                'label' => 'Type AEEEV',
                'columnid' => 8,
                'length' => 10,
                'field' => 'select',
                'sample_value' => 'TC',
                'active' => true,
                'select' => true,
                'options' => [
                    [
                        'name' => 'CM',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TD',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TP',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TPa',
                        'selected' => false,
                    ],
                    [
                        'name' => 'TC',
                        'selected' => false,
                    ],
                    [
                        'name' => 'AAS',
                        'selected' => false,
                    ],
                    [
                        'name' => 'FMP',
                        'selected' => false,
                    ],
                ],
            ],
            '3' => [
                'column' => 'sequence',
                'type' => PARAM_INT,
                'label' => 'Sequence dans le module',
                'columnid' => 12,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '',
                'active' => true,
                'min' => 0,
                'max' => 1000,
            ],
            '4' => [
                'column' => 'intitule_seance',
                'type' => PARAM_TEXT,
                'label' => 'Intitulé de la séance / de l’exercice',
                'columnid' => 19,
                'length' => 255,
                'field' => 'text',
                'sample_value' => '...',
                'active' => true,
            ],
            '5' => [
                'column' => 'cm',
                'type' => PARAM_FLOAT,
                'label' => 'CM',
                'columnid' => 20,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '6' => [
                'column' => 'td',
                'type' => PARAM_FLOAT,
                'label' => 'TD',
                'columnid' => 21,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '7' => [
                'column' => 'tp',
                'type' => PARAM_FLOAT,
                'label' => 'TP',
                'columnid' => 22,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '8' => [
                'column' => 'tpa',
                'type' => PARAM_FLOAT,
                'label' => 'TPa',
                'columnid' => 23,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '9' => [
                'column' => 'tc',
                'type' => PARAM_INT,
                'label' => 'TC',
                'columnid' => 24,
                'length' => 10,
                'field' => 'int',
                'sample_value' => '0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '10' => [
                'column' => 'aas',
                'type' => PARAM_FLOAT,
                'label' => 'AAS',
                'columnid' => 25,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '11' => [
                'column' => 'fmp',
                'type' => PARAM_FLOAT,
                'label' => 'FMP',
                'columnid' => 26,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '12' => [
                'column' => 'perso_av',
                'type' => PARAM_FLOAT,
                'label' => 'Perso av',
                'columnid' => 27,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
            ],
            '13' => [
                'column' => 'perso_ap',
                'type' => PARAM_FLOAT,
                'label' => 'Perso ap',
                'columnid' => 28,
                'length' => 10,
                'field' => 'float',
                'sample_value' => '0,0',
                'active' => true,
                'min' => 0,
                'max' => 99,
            ],
            '14' => [
                'column' => 'consignes',
                'type' => PARAM_TEXT,
                'label' => 'Consignes de travail pour préparer la séance',
                'columnid' => 29,
                'length' => 10,
                'field' => 'select',
                'sample_value' => '...',
                'active' => true,
            ],
            '15' => [
                'column' => 'supports',
                'type' => PARAM_TEXT,
                'label' => 'Supports pédagogiques essentiels',
                'columnid' => 30,
                'length' => 10,
                'field' => 'select',
                'sample_value' => '...',
                'active' => true,
            ],
        ];
        return $columns;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Courseid', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Execute and return json data.
     *
     * @param int $courseid - The course id.
     * @return array $data - The data in JSON format
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $courseid): array {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'courseid' => $courseid,
            ]
        );
        self::validate_context(context_system::instance());
        $courseid = $params['courseid'];

        $data = [
            'modules' => programme::get_data($courseid),
        ];

        return $data;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'modules' => new external_multiple_structure(
                new external_single_structure([
                    'moduleid' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                    'modulesortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                    'modulename' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                    'rows' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_REQUIRED),
                            'cells' => new external_multiple_structure(
                                new external_single_structure([
                                    'column' => new external_value(PARAM_TEXT, 'Column id', VALUE_REQUIRED),
                                    'value' => new external_value(PARAM_TEXT, 'Value', VALUE_REQUIRED),
                                    'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
                                ])
                            ),
                            'disciplines' => new external_multiple_structure(
                                new external_single_structure([
                                    'id' => new external_value(PARAM_INT, 'Id', VALUE_REQUIRED),
                                    'name' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                                    'percentage' => new external_value(PARAM_FLOAT, 'Value', VALUE_REQUIRED),
                                ])
                            ),
                        ])
                    ),
                    'columns' => new external_multiple_structure(
                        new external_single_structure([
                            'column' => new external_value(PARAM_TEXT, 'Column id', VALUE_REQUIRED),
                            'type' => new external_value(PARAM_TEXT, 'Type', VALUE_REQUIRED),
                            'float' => new external_value(PARAM_BOOL, 'Float', VALUE_OPTIONAL),
                            'int' => new external_value(PARAM_BOOL, 'Int', VALUE_OPTIONAL),
                            'text' => new external_value(PARAM_BOOL, 'Text', VALUE_OPTIONAL),
                            'select' => new external_value(PARAM_BOOL, 'Select', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_BOOL, 'Visible', VALUE_REQUIRED),
                            'label' => new external_value(PARAM_TEXT, 'Label', VALUE_REQUIRED),
                            'columnid' => new external_value(PARAM_INT, 'Column id', VALUE_REQUIRED),
                            'length' => new external_value(PARAM_INT, 'Length', VALUE_REQUIRED),
                            'field' => new external_value(PARAM_TEXT, 'Field', VALUE_REQUIRED),
                            'sample_value' => new external_value(PARAM_TEXT, 'Sample value', VALUE_REQUIRED),
                            'min' => new external_value(PARAM_INT, 'Min', VALUE_OPTIONAL),
                            'max' => new external_value(PARAM_INT, 'Max', VALUE_OPTIONAL),
                            'options' => new external_multiple_structure(
                                new external_single_structure([
                                    'name' => new external_value(PARAM_TEXT, 'Name', VALUE_REQUIRED),
                                    'selected' => new external_value(PARAM_BOOL, 'Selected', VALUE_REQUIRED),
                                ]), 'Option', VALUE_OPTIONAL
                            ),
                        ])
                    ),
                ])
            ),
        ]);
    }
}

