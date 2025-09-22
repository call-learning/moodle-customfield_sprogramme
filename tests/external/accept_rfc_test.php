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
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\programme_manager;
use customfield_sprogramme\test\testcase_helper_trait;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the accept_rfc class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\accept_rfc
 */
final class accept_rfc_test extends \externallib_advanced_testcase {
    use testcase_helper_trait;

    /**
     * Test execute with wrong parameters
     */
    public function test_execute_wrong_parameters(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->expectException(\invalid_parameter_exception::class);
        $this->accept_rfc(1234, get_admin()->id);

        $this->expectException(\invalid_parameter_exception::class);
        $this->accept_rfc(1234, 5678);
    }

    /**
     * Test execute with correct parameters
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        [
            'users' => $users,
            'sampleprogrammedata' => $sampleprogrammedata,
            'cfdataid' => $cfdataid,
        ] = $this->setup_course_and_rfc();
        $this->setUser($users[0]);
        $accepted = $this->accept_rfc($cfdataid, $users[0]->id);
        $this->assertFalse($accepted);
        $accepted = $this->accept_rfc($cfdataid, $users[1]->id);
        $this->assertTrue($accepted);
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_ACCEPTED]));
        $programmemanager = new programme_manager($cfdataid);
        $this->assertTrue($programmemanager->has_data());
        $this->assert_programme_data_equals($sampleprogrammedata, $programmemanager->get_data());
    }

    /**
     * Test execute with correct parameters
     */
    public function test_with_wrong_role(): void {
        global $CFG;
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        ['course' => $course, 'cfdataid' => $cfdataid, 'users' => $users] = $this->setup_course_and_rfc();
        $user3 = $this->getDataGenerator()->create_and_enrol($course);
        $this->expectExceptionMessage('customfield_sprogramme/rfcacceptancenotallowed');
        $this->setUser($user3);
        $this->accept_rfc($cfdataid, $users[1]->id);
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function accept_rfc(...$params) {
        $acceptrfc = accept_rfc::execute(...$params);
        return external_api::clean_returnvalue(accept_rfc::execute_returns(), $acceptrfc);
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
        $pgenerator->create_rfc(
            $cfdata->get('id'),
            userid: $users[1]->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($sampleprogrammedata[0])
        );
        return [
            'course' => $course,
            'users' => $users,
            'sampleprogrammedata' => $sampleprogrammedata[0],
            'cfdataid' => $cfdata->get('id'),
        ];
    }
}
