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
use customfield_sprogramme\local\persistent\sprogramme_visa;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the accept_visa class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2026 CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\accept_visa
 */
final class accept_visa_test extends \externallib_advanced_testcase {
    /**
     * Helper.
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function accept_visa(...$params) {
        $accepted = accept_visa::execute(...$params);
        return external_api::clean_returnvalue(accept_visa::execute_returns(), $accepted);
    }

    /**
     * Test execute with wrong parameters.
     */
    public function test_execute_wrong_parameters(): void {
        $this->resetAfterTest();
        $this->expectException(\invalid_parameter_exception::class);
        $this->accept_visa(1234, 'x');
    }

    /**
     * Test execute.
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        [
            'rfcid' => $rfcid,
            'reviewer' => $reviewer,
        ] = $this->setup_visa_data();
        $this->setUser($reviewer);
        $result = $this->accept_visa($rfcid, 'Looks good');
        $this->assertTrue($result);
        $visa = sprogramme_visa::get_record(['rfcid' => $rfcid, 'visauser' => $reviewer->id]);
        $this->assertNotEmpty($visa);
        $this->assertEquals(sprogramme_visa::STATUS_APPROVED, $visa->get('status'));
        $this->assertEquals('Looks good', $visa->get('comment'));
    }

    /**
     * Test execute with user not allowed to visa.
     */
    public function test_execute_with_wrong_role(): void {
        $this->resetAfterTest();
        [
            'rfcid' => $rfcid,
            'course' => $course,
        ] = $this->setup_visa_data();
        $regular = $this->getDataGenerator()->create_and_enrol($course);
        $this->setUser($regular);
        $this->expectExceptionMessage('customfield_sprogramme/visaacceptancenotallowed');
        $this->accept_visa($rfcid, 'Nope');
    }

    /**
     * Setup a submitted RFC with one reviewer role available.
     *
     * @return array
     */
    private function setup_visa_data(): array {
        $generator = $this->getDataGenerator();
        $cfgenerator = $generator->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();
        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $course = $generator->create_course();
        $cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);
        $pgenerator = $generator->get_plugin_generator('customfield_sprogramme');
        $rfc = $pgenerator->create_rfc($cfdata->get('id'), \customfield_sprogramme\local\persistent\sprogramme_rfc::RFC_SUBMITTED, '{}');

        set_config('responsiblerolename', 'spg_reviewer', 'customfield_sprogramme');
        set_config('departmentheadrolename', 'spg_hod', 'customfield_sprogramme');
        $reviewerroleid = $generator->create_role([
            'shortname' => 'spg_reviewer',
            'name' => 'SProgramme Reviewer',
            'archetype' => 'editingteacher',
        ]);
        $generator->create_role([
            'shortname' => 'spg_hod',
            'name' => 'SProgramme HOD',
            'archetype' => 'teacher',
        ]);

        $reviewer = $generator->create_user();
        $generator->enrol_user($reviewer->id, $course->id, $reviewerroleid);

        return [
            'rfcid' => $rfc->id,
            'reviewer' => $reviewer,
            'course' => $course,
        ];
    }
}
