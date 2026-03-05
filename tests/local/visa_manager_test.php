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

namespace customfield_sprogramme\local;

use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\persistent\sprogramme_visa;

/**
 * Functional test for visa manager.
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\visa_manager
 */
final class visa_manager_test extends \advanced_testcase {
    /**
     * @var \core_customfield\category_controller The custom field category controller.
     */
    protected \core_customfield\category_controller $cfcat;
    /**
     * @var \core_customfield\field_controller The custom field instance.
     */
    protected \core_customfield\field_controller $cfield;

    /**
     * @var \core_customfield\data_controller The custom field data.
     */
    protected \core_customfield\data_controller $cfdata;
    /**
     * @var \stdClass The course.
     */
    protected \stdClass $course;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $this->cfcat = $cfgenerator->create_category();
        $this->cfield = $cfgenerator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $this->course = $this->getDataGenerator()->create_course();
        $this->cfdata = $cfgenerator->add_instance_data($this->cfield, $this->course->id, 1);
        $this->setUser($this->getDataGenerator()->create_user('user1'));
    }

    /**
     * Create a RFC for the current custom field data.
     *
     * @return \stdClass
     */
    private function create_rfc(): \stdClass {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        return $pgenerator->create_rfc($this->cfdata->get('id'), sprogramme_rfc::RFC_SUBMITTED, '{}');
    }

    /**
     * Test invalid rfc id.
     */
    public function test_invalid_rfcid(): void {
        $this->expectException(\moodle_exception::class);
        new visa_manager(999999);
    }

    /**
     * Test can_visa and datafield id.
     */
    public function test_can_visa_and_datafield_id(): void {
        $rfc = $this->create_rfc();
        $generator = $this->getDataGenerator();
        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $generator->create_role(
            [
                'shortname' => $responsiblerolename,
                'name' => 'Responsible',
                'archetype' => 'editingteacher',
            ]
        );
        $responsible1 = $generator->create_and_enrol($this->course, $responsiblerolename);
        $responsible2 = $generator->create_and_enrol($this->course, $responsiblerolename);
        $regular = $generator->create_and_enrol($this->course);

        $visamanager = new visa_manager($rfc->id);
        $this->assertTrue($visamanager->can_visa($responsible1->id));
        $this->assertTrue($visamanager->can_visa($responsible2->id));
        $this->assertFalse($visamanager->can_visa($regular->id));
        $this->assertEquals($this->cfdata->get('id'), $visamanager->get_datafield_id());
    }

    /**
     * Test total todo using responsible and head of department role assignments.
     */
    public function test_get_visas_total_todo(): void {
        $rfc = $this->create_rfc();
        $generator = $this->getDataGenerator();

        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $generator->create_role(
            [
                'shortname' => $responsiblerolename,
                'name' => 'Responsible',
                'archetype' => 'editingteacher',
            ]
        );
        $generator->create_and_enrol($this->course, $responsiblerolename);
        $generator->create_and_enrol($this->course, $responsiblerolename);

        $departmentheadrolename = get_config('customfield_sprogramme', 'departmentheadrolename');
        $hodroleid = $generator->create_role(
            [
                'shortname' => $departmentheadrolename,
                'name' => 'Head of Department',
                'archetype' => 'teacher',
            ]
        );
        $hoduser = $generator->create_user([
            'username' => 'headofdepartment1',
            'email' => 'headofdepartment@example.com',
            'firstname' => 'Head of',
            'lastname' => 'Department',
        ]);
        $generator->role_assign($hodroleid, $hoduser->id, \context_coursecat::instance($this->course->category));

        $visamanager = new visa_manager($rfc->id);
        $this->assertEquals(3, $visamanager->get_visas_total_todo());
    }

    /**
     * Test accept/reject and visa data.
     */
    public function test_accept_reject_and_get_visa_data(): void {
        $rfc = $this->create_rfc();
        $visamanager = new visa_manager($rfc->id);

        $user1 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'One']);
        $user2 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'Two']);
        $user3 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'Three']);

        $visamanager->accept_visa($user1->id, 'approved');
        $visamanager->reject_visa($user2->id, 'rejected');
        $visa = new sprogramme_visa(0, (object) [
            'rfcid' => $rfc->id,
            'visauser' => $user3->id,
            'comment' => '',
            'status' => sprogramme_visa::STATUS_PENDING,
        ]);
        $visa->create();

        $visas = $visamanager->get_visas();
        $this->assertCount(3, $visas);

        $visasbyuser = [];
        foreach ($visas as $record) {
            $visasbyuser[$record->get('visauser')] = $record;
        }
        $this->assertEquals(sprogramme_visa::STATUS_APPROVED, $visasbyuser[$user1->id]->get('status'));
        $this->assertEquals('approved', $visasbyuser[$user1->id]->get('comment'));
        $this->assertEquals(sprogramme_visa::STATUS_REJECTED, $visasbyuser[$user2->id]->get('status'));
        $this->assertEquals('rejected', $visasbyuser[$user2->id]->get('comment'));
        $this->assertEquals(sprogramme_visa::STATUS_PENDING, $visasbyuser[$user3->id]->get('status'));

        $this->assertEquals(2, $visamanager->get_visas_total_done());

        $this->setUser($user1);
        $data = $visamanager->get_visa_data();
        $this->assertEquals($rfc->id, $data['rfcid']);
        $this->assertEquals(0, $data['todo']);
        $this->assertEquals(1, $data['approved']);
        $this->assertEquals(1, $data['rejected']);
        $this->assertCount(3, $data['visas']);

        $user1data = array_values(array_filter(
            $data['visas'],
            fn($item) => $item['visauser']['id'] === $user1->id
        ));
        $user2data = array_values(array_filter(
            $data['visas'],
            fn($item) => $item['visauser']['id'] === $user2->id
        ));
        $this->assertCount(1, $user1data);
        $this->assertCount(1, $user2data);
        $this->assertEquals(fullname($user1), $user1data[0]['visauser']['fullname']);
        $this->assertEquals(get_string('visaapproved', 'customfield_sprogramme'), $user1data[0]['statustext']);
        $this->assertEquals(get_string('visarejected', 'customfield_sprogramme'), $user2data[0]['statustext']);
    }
}
