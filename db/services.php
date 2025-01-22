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

/**
 * External functions and service declaration for local_nixen
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    customfield_sprogramme
 * @category   webservice
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'customfield_sprogramme_get_columns' => [
        'classname' => \customfield_sprogramme\external\get_columns::class,
        'methodname' => 'execute',
        'description' => 'Get the contents of a json file',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:view',
    ],
    'customfield_sprogramme_get_data' => [
        'classname' => \customfield_sprogramme\external\get_data::class,
        'methodname' => 'execute',
        'description' => 'Get the contents of the columns',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:view',
    ],
    'customfield_sprogramme_set_data' => [
        'classname' => \customfield_sprogramme\external\set_data::class,
        'methodname' => 'execute',
        'description' => 'Set the contents of the columns',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
    'customfield_sprogramme_create_row' => [
        'classname' => \customfield_sprogramme\external\create_row::class,
        'methodname' => 'execute',
        'description' => 'Create a new row',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
    'customfield_sprogramme_create_module' => [
        'classname' => \customfield_sprogramme\external\create_module::class,
        'methodname' => 'execute',
        'description' => 'Create a new module',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
    'customfield_sprogramme_delete_module' => [
        'classname' => \customfield_sprogramme\external\delete_module::class,
        'methodname' => 'execute',
        'description' => 'Delete a module',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
    'customfield_sprogramme_delete_row' => [
        'classname' => \customfield_sprogramme\external\delete_row::class,
        'methodname' => 'execute',
        'description' => 'Delete a row',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
    'customfield_sprogramme_update_sort_order' => [
        'classname' => \customfield_sprogramme\external\update_sort_order::class,
        'methodname' => 'execute',
        'description' => 'Update the sort order',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'customfield/sprogramme:edit',
    ],
];
