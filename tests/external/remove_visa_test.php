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
use customfield_sprogramme\local\visa_manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the remove_visa class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2026 CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\remove_visa
 */
final class remove_visa_test extends \externallib_advanced_testcase {
    /**
     * Helper.
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function remove_visa(...$params) {
        $removed = remove_visa::execute(...$params);
        return external_api::clean_returnvalue(remove_visa::execute_returns(), $removed);
    }

    /**
     * Test execute with wrong parameters.
     */
    public function test_execute_wrong_parameters(): void {
        $this->resetAfterTest();
        $this->expectException(\invalid_parameter_exception::class);
        $this->remove_visa(1234);
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
        $result = $this->remove_visa($rfcid);
        $this->assertTrue($result);
        $this->assertFalse(sprogramme_visa::record_exists_select('rfcid = :rfcid AND visauser = :userid', [
            'rfcid' => $rfcid,
            'userid' => $reviewer->id,
        ]));
    }

    /**
     * Setup a submitted RFC with one reviewer visa.
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

        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $responsibleroleid = $generator->create_role([
            'shortname' => $responsiblerolename,
            'name' => 'Responsible',
            'archetype' => 'editingteacher',
        ]);
        $departmentheadrolename = get_config('customfield_sprogramme', 'departmentheadrolename');
        $generator->create_role([
            'shortname' => $departmentheadrolename,
            'name' => 'Head of Department',
            'archetype' => 'teacher',
        ]);

        $reviewer = $generator->create_user();
        $generator->enrol_user($reviewer->id, $course->id, $responsibleroleid);

        $visamanager = new visa_manager($rfc->id);
        $visamanager->accept_visa($reviewer->id, 'ok');

        return [
            'rfcid' => $rfc->id,
            'reviewer' => $reviewer,
        ];
    }
}
