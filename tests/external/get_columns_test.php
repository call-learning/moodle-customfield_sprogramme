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

namespace external;

use core_external\external_api;
use customfield_sprogramme\external\accept_rfc;
use customfield_sprogramme\external\get_columns;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the get_columns class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\get_columns
 */
final class get_columns_test extends \externallib_advanced_testcase {
    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_columns(...$params) {
        $acceptrfc = get_columns::execute(...$params);
        return external_api::clean_returnvalue(get_columns::execute_returns(), $acceptrfc);
    }

    /**
     * Test execute with correct parameters
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();
        $cfield = $cfgenerator->create_field(['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']);
        $course = $this->getDataGenerator()->create_course();
        $cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);
        $this->setAdminUser();
        $columns = $this->get_columns($cfdata->get('id'));
        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
    }
}
