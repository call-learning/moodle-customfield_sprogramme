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

use core_external\external_api;
use customfield_sprogramme\external\update_sort_order;
use customfield_sprogramme\local\programme_manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the update_sort_order class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\update_sort_order
 */
final class update_sort_order_test extends \externallib_advanced_testcase {
    /**
     * Test the sort order update.
     */
    public function test_sort_order(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');

        $sampledata = get_sample_programme_data();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();
        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $course = $this->getDataGenerator()->create_course();
        $cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);

        $pm = new programme_manager($cfdata->get('id'));
        $pgenerator->create_programme($cfdata->get('id'), $sampledata[0]);

        $data = $pm->get_data();
        $rowids = array_column($data[0]['rows'], 'id');
        $this->assertEquals(array_combine($rowids, [1, 2]), array_column($data[0]['rows'], 'sortorder', 'id'));

        $data[0]['rows'][0]['sortorder'] = 5;
        $pm->set_data($data);
        $data = $pm->get_data();
        $this->assertEquals(array_combine($rowids, [5, 2]), array_column($data[0]['rows'], 'sortorder', 'id'));

        $this->setAdminUser();
        $this->update_sort_order(
            'row',
            $cfdata->get('id'),
            $data[0]['moduleid'],
            $data[0]['rows'][0]['id'],
            $data[0]['rows'][1]['id']
        );
        $data = $pm->get_data();
        $this->assertEquals(array_combine($rowids, [5, 6]), array_column($data[0]['rows'], 'sortorder', 'id'));
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function update_sort_order(...$params) {
        $acceptrfc = update_sort_order::execute(...$params);
        return external_api::clean_returnvalue(update_sort_order::execute_returns(), $acceptrfc);
    }
}
