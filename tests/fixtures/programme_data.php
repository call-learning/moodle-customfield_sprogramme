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

function get_sample_programme_data(): array {
    $programmedata = [];
    $programmedata[] =
        [
            [
                'moduleid' => -1,
                'modulesortorder' => 0,
                'modulename' => 'Test Module 1',
                'deleted' => false,
                'rows' =>
                    [
                        [
                            'id' => -1,
                            'deleted' => false,
                            'sortorder' => 1,
                            'cells' =>
                                [

                                    [
                                        'column' => 'dd_rse',
                                        'value' => null,
                                        'type' => 'select',
                                        'group' => '',
                                        'oldvalue' => null,
                                    ],

                                    [
                                        'column' => 'intitule_seance',
                                        'value' => 'Seance 1',
                                        'type' => 'text',
                                        'group' => '',
                                        'oldvalue' => 'Seance 1 old title',
                                    ],
                                    [
                                        'column' => 'cm',
                                        'value' => '15',
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => '15',
                                    ],
                                    [
                                        'column' => 'td',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'tp',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'tpa',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'tc',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'aas',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'fmp',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'perso_av',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => '',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'perso_ap',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => '',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'consignes',
                                        'value' => null,
                                        'type' => 'text',
                                        'group' => '',
                                        'oldvalue' => null,
                                    ],
                                    [
                                        'column' => 'supports',
                                        'value' => null,
                                        'type' => 'text',
                                        'group' => '',
                                        'oldvalue' => null,
                                    ],
                                ],
                            'disciplines' =>
                                [
                                ],
                            'competencies' =>
                                [
                                ],
                        ],
                        [
                            'id' => -1,
                            'deleted' => false,
                            'sortorder' => 2,
                            'cells' =>
                                [

                                    [
                                        'column' => 'dd_rse',
                                        'value' => 'Sans lien avec DD/RSE',
                                        'type' => 'select',
                                        'group' => '',
                                    ],
                                    [
                                        'column' => 'intitule_seance',
                                        'value' => 'Seance 2',
                                        'type' => 'text',
                                        'group' => '',
                                    ],
                                    [
                                        'column' => 'cm',
                                        'value' => 10.5,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'td',
                                        'value' => '46',
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'tp',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'tpa',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'tc',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'aas',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'fmp',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => 'unique',
                                    ],
                                    [
                                        'column' => 'perso_av',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => '',
                                    ],
                                    [
                                        'column' => 'perso_ap',
                                        'value' => null,
                                        'type' => 'float',
                                        'group' => '',
                                    ],
                                    [
                                        'column' => 'consignes',
                                        'value' => null,
                                        'type' => 'text',
                                        'group' => '',
                                    ],
                                    [
                                        'column' => 'supports',
                                        'value' => null,
                                        'type' => 'text',
                                        'group' => '',
                                    ],
                                ],
                            'disciplines' =>
                                [
                                    [
                                        'id' => 1,
                                        'percentage' => 50.0,
                                    ],
                                    [
                                        'id' => 2,
                                        'percentage' => 50.0,
                                    ],
                                ],
                            'competencies' =>
                                [
                                    [
                                        'id' => 1,
                                        'percentage' => 100.0,
                                    ],
                                ],
                        ],
                    ],
            ],
        ];
    return $programmedata;
}
