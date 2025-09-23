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
use customfield_sprogramme\external\get_data;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\test\testcase_helper_trait;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the update_course class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\get_data
 */
final class get_data_test extends \externallib_advanced_testcase {
    use testcase_helper_trait;

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_data(...$params) {
        $getdata = get_data::execute(...$params);
        return external_api::clean_returnvalue(get_data::execute_returns(), $getdata);
    }


    /**
     * Test execute with wrong parameters
     */
    public function test_execute_wrong_parameters(): void {
        $this->resetAfterTest();
        $this->expectException(\invalid_parameter_exception::class);
        $this->get_data(1234, true);
        $this->setAdminUser();
        $this->expectException(\invalid_parameter_exception::class);
        $this->get_data(1234, false);
    }

    /**
     * Test execute with correct parameters
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        [
            'users' => $users,
            'cfdataid' => $cfdataid,
        ] = $this->setup_course_and_rfc();
        $data = $this->get_data($cfdataid);
        $this->assertEquals('Test Module 1', $data['modules'][0]['modulename']);
        $this->assertEquals(15, $data['modules'][0]['rows'][0]['cells'][2]['value']);

        $this->setAdminUser(); // We need to be admin to see all the rfc data.
        $data = $this->get_data($cfdataid, true);
        $this->assertNotEmpty($data['modules']); // We get the data from the rfc.
        $this->assertNotEmpty($data['rfc']);
        $this->assertEquals('A different title', $data['modules'][0]['modulename']);
        $this->assertEquals(18, $data['modules'][0]['rows'][0]['cells'][2]['value']);
    }

    /**
     * Setup a course with a sprogramme field and a rfc
     *
     * @return array
     */
    private function setup_course_and_rfc() {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();

        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $sampleprogrammedata = get_sample_programme_data();
        $course = $this->getDataGenerator()->create_course();
        $cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'manager');
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_programme(
            $cfdata->get('id'),
            $sampleprogrammedata[0]
        );
        $programmemanager = new programme_manager($cfdata->get('id'));
        $data = $programmemanager->get_data(); // To get the snapshot created.
        // Modify slightly the data, so for example the user can see a difference.
        $data[0]['modulename'] = 'A different title';
        $data[0]['rows'][0]['cells'][2]['value'] = 18; // Change cm information.
        $pgenerator->create_rfc(
            $cfdata->get('id'),
            userid: $users[1]->id,
            snapshot: json_encode($data),
            type: sprogramme_rfc::RFC_SUBMITTED,
        );
        return [
            'course' => $course,
            'users' => $users,
            'sampleprogrammedata' => $sampleprogrammedata[0],
            'cfdataid' => $cfdata->get('id'),
        ];
    }
}
