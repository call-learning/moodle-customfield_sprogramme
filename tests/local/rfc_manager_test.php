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
use customfield_sprogramme\test\testcase_helper_trait;

/**
 * Functional test for rfc class
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\rfc_manager
 */
final class rfc_manager_test extends \advanced_testcase {
    use testcase_helper_trait;

    /**
     * @var \core_customfield\category_controller The custom field category controller.
     */
    protected \core_customfield\category_controller $cfcat;
    /**
     * @var \core_customfield\field_controller The custom field instance.
     */
    protected \core_customfield\field_controller $cfield;

    /**
     * @var array The custom field data.
     */
    protected \core_customfield\data_controller $cfdata;
    /**
     * @var \stdClass The courses.
     */
    protected \stdClass $course;

    /**
     * @var array Sample programme data.
     */
    protected array $sampleprogrammedata;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        $this->sampleprogrammedata = get_sample_programme_data();
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
     * Test removing a rfc
     */
    public function test_remove(): void {
        $this->setAdminUser();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'One']);
        $user2 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'Two']);
        $this->setUser($user1);
        $pgenerator->create_rfc($this->cfdata->get('id'));
        $this->setUser($user2);
        $pgenerator->create_rfc($this->cfdata->get('id'));
        // Just check to be sure they are created.
        $this->assertCount(2, sprogramme_rfc::get_records());
        $this->setAdminUser();
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->assertTrue($rfcmanager->remove($user1->id));
        $this->assertCount(1, sprogramme_rfc::get_records());
        $this->assertTrue($rfcmanager->remove($user2->id));
        $this->assertCount(0, sprogramme_rfc::get_records());
        $this->assertFalse($rfcmanager->remove($user2->id));
    }

    /**
     * Test accepting a rfc
     */
    public function test_accept(): void {
        $this->setAdminUser();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_and_enrol($this->course);
        $user2 = $this->getDataGenerator()->create_and_enrol($this->course);
        $user3 = $this->getDataGenerator()->create_and_enrol($this->course);
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $teacher2 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->setUser($user1);
        $pgenerator->create_rfc($this->cfdata->get('id'), type: sprogramme_rfc::RFC_SUBMITTED);
        $this->setUser($user2);
        $pgenerator->create_rfc($this->cfdata->get('id'), userid: $teacher1->id, type: sprogramme_rfc::RFC_SUBMITTED);
        $this->setUser($user3);
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher2->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );
        // Just check to be sure they are created.
        $this->assertCount(3, sprogramme_rfc::get_records());
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $programmemanager = new programme_manager($this->cfdata->get('id'));
        $this->setAdminUser();
        $this->assertEquals(0, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_ACCEPTED]));
        $this->assertFalse($rfcmanager->accept($user1->id)); // User 1 is not the admin any rfc.
        $this->assertFalse($rfcmanager->accept($user3->id)); // User 3 is an admin but there was no snapshot.
        $this->assertEquals(0, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_ACCEPTED]));
        $this->assertFalse($rfcmanager->accept($teacher1->id)); // Programme data is missing in the snapshot.
        $this->assertFalse($programmemanager->has_data());
        // Now the rfc has been accepted, the programme data should be set.
        $this->setAdminUser();
        $this->assertTrue($rfcmanager->accept($teacher2->id)); // Programme data is present in the snapshot.
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_ACCEPTED]));
        $this->assertTrue($programmemanager->has_data());
        $this->assert_programme_data_equals($this->sampleprogrammedata[0], $programmemanager->get_data());
    }

    /**
     * Test if we can edit
     */
    public function test_can_edit(): void {
        global $CFG;
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_and_enrol($this->course);
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $teacher2 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher2->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );

        $this->setUser($user1);
        $this->assertFalse($rfcmanager->can_edit());
        $this->setUser($teacher1);
        $this->assertFalse($rfcmanager->can_edit()); // We submitted a rfc, we cannot edit now.
        $this->setAdminUser();
        // Accept the rfc.
        $rfcmanager->accept($teacher2->id); // Programme data is present in the snapshot.
        $this->setUser($teacher2);
        $this->assertTrue($rfcmanager->can_edit()); // Now we can edit.
    }

    /**
     * Test if we can add
     */
    public function test_can_add(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_and_enrol($this->course);
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $teacher2 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher2->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );

        $this->setUser($user1);
        $this->assertFalse($rfcmanager->can_add());
        $this->setUser($teacher1);
        $this->assertFalse($rfcmanager->can_add()); // We submitted a rfc, we cannot edit now.
        $this->setAdminUser();
        // Accept the rfc.
        $rfcmanager->accept($teacher2->id); // Programme data is present in the snapshot.
        $this->setUser($teacher2);
        $this->assertTrue($rfcmanager->can_add()); // Now we can edit.
    }

    /**
     * Test if has submitted
     */
    public function test_has_submitted(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->assertFalse($rfcmanager->has_submitted());
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
        );
        $this->assertTrue($rfcmanager->has_submitted());
    }

    /**
     * Test creating a rfc
     */
    public function test_create(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->setUser($teacher);
        $created = $rfcmanager->create(
            [
                'modulename' => 'Module 1',
            ]
        );
        $this->assertCount(1, sprogramme_rfc::get_records());
        $this->assertEquals($teacher->id, $created->get('adminid'));
        $this->assertEquals($this->cfdata->get('id'), $created->get('datafieldid'));
        $this->assertEquals(sprogramme_rfc::RFC_REQUESTED, $created->get('type'));
        $this->assertEquals('{"modulename":"Module 1"}', $created->get('snapshot'));

        // Now create another one, it should return the existing one.
        $rfc2 = $rfcmanager->create(
            [
                'modulename' => 'Module 2',
            ]
        );
        $this->assertCount(1, sprogramme_rfc::get_records());
        $this->assertEquals($created->get('id'), $rfc2->get('id'));
        $this->assertEquals('{"modulename":"Module 2"}', $rfc2->get('snapshot'));
    }

    /**
     * Test cancelling a rfc
     */
    public function test_cancel(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->assertFalse($rfcmanager->has_submitted());
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
        );

        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_SUBMITTED]));
        $rfcmanager->cancel($teacher->id);
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_CANCELLED]));
    }

    /**
     * Test cancelling a rfc
     */
    public function test_submit(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->assertFalse($rfcmanager->has_submitted());
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher->id,
        );
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_REQUESTED]));
        $rfcmanager->submit($teacher->id);
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_SUBMITTED]));
    }

    /**
     * Test getting the data from a rfc
     */
    public function test_get_data(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $teacher2 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $manager = $this->getDataGenerator()->create_and_enrol($this->course, 'manager');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->setUser($teacher1);
        $data = $rfcmanager->get_data();
        $this->assertEmpty($data);
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher1->id,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );
        $data = $rfcmanager->get_data();
        $this->assertFalse($data['issubmitted']);
        $this->assertFalse($data['canaccept']); // Teacher 1 is not admin so cannot accept.
        $this->assertTrue($data['cansubmit']); // Teacher 1 can submit.
        $this->assertFalse($data['cancancel']); // Teacher 1 cannot cancel as nothing submitted.
        $rfcmanager->submit($teacher1->id);
        $data = $rfcmanager->get_data();
        $this->assertTrue($data['issubmitted']);
        $this->assertFalse($data['canaccept']); // Teacher 1 is not admin so cannot accept.
        $this->assertFalse($data['cansubmit']); // Teacher 1 can not submit again.
        $this->assertTrue($data['cancancel']); // Teacher 1 should be able to cancel now.

        $this->setUser($teacher2);
        $data = $rfcmanager->get_data();
        $this->assertTrue($data['issubmitted']);
        $this->assertFalse($data['canaccept']); // Teacher 2 is not admin so cannot accept.
        $this->assertFalse($data['cansubmit']); // Teacher 2 can not submit as Teacher 1 has already submitted.
        $this->assertFalse($data['cancancel']); // Teacher 2 cannot cancel as Teacher 1 has already submitted.
        $this->setUser($manager);
        $data = $rfcmanager->get_data();
        $this->assertTrue($data['issubmitted']);
        $this->assertTrue($data['canaccept']); // Manager is admin so can accept.
        $this->assertFalse($data['cansubmit']); // Manager cannot submit.
        $this->assertFalse($data['cancancel']); // Manager can cancel.
    }

    /**
     * Test rejecting a rfc
     */
    public function test_reject(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $this->assertFalse($rfcmanager->has_submitted());
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
        );

        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_SUBMITTED]));
        $rfcmanager->reject($teacher->id);
        $this->assertEquals(1, sprogramme_rfc::count_records(['type' => sprogramme_rfc::RFC_REJECTED]));
    }

    /**
     * Test is required
     *
     * @param string $usersubmitted The user who submitted the rfc.
     * @param string $useradmin The user who is admin.
     * @param int $rfcstatus The status of the rfc.
     * @param array $expected The expected result.
     *
     * @dataProvider get_current_provider
     */
    public function test_get_current(string $usersubmitted, string $useradmin, int $rfcstatus, array $expected): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $users = [];
        $users['teacher1'] = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $users['teacher2'] = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $users['manager'] = $this->getDataGenerator()->create_and_enrol($this->course, 'manager');
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));

        $this->setUser($users[$usersubmitted]);
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $users[$useradmin]->id,
            type: $rfcstatus,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );
        foreach ($expected as $user => $hasrfc) {
            $this->setUser($users[$user]);
            if ($hasrfc) {
                $this->assertNotNull($rfcmanager->get_current(), "User $user should have a rfc");
            } else {
                $this->assertEmpty($rfcmanager->get_current(), "User $user should not have a rfc");
            }
        }
    }

    /**
     * Data provider for test_get_current
     *
     * @return array
     */
    public static function get_current_provider(): array {
        return [
            'Teacher 1 requested a rfc' => [
                'usersubmitted' => 'teacher1',
                'useradmin' => 'manager',
                'rfcstatus' => sprogramme_rfc::RFC_REQUESTED,
                'expected' => [
                    'teacher1' => true, // Current rfc is retured.
                    'teacher2' => false,
                    'manager' => false, // Manager cannot see the rfc as it is not submitted yet.
                ],
            ],
            'Teacher 1 submitted a rfc' => [
                'usersubmitted' => 'teacher1',
                'useradmin' => 'manager',
                'rfcstatus' => sprogramme_rfc::RFC_SUBMITTED,
                'expected' => [
                    'teacher1' => true,
                    'teacher2' => true, // Teacher 2 can see that there is a submitted rfc.
                    'manager' => true, // Manager can see the submitted rfc.
                ],
            ],
            'Teacher 1 cancelled a rfc' => [
                'usersubmitted' => 'teacher1',
                'useradmin' => 'manager',
                'rfcstatus' => sprogramme_rfc::RFC_CANCELLED,
                'expected' => [
                    'teacher1' => false,
                    'teacher2' => false,
                    'manager' => true, // Manager can see the rfc as it the manager that is the admin.
                ],
            ],
            'Teacher 1 cancelled a rfc managed by teacher 2' => [
                'usersubmitted' => 'teacher1',
                'useradmin' => 'teacher2',
                'rfcstatus' => sprogramme_rfc::RFC_CANCELLED,
                'expected' => [
                    'teacher1' => false,
                    'teacher2' => true, // Teacher 2 can see the cancelled rfc as it is the admin.
                    'manager' => false, // Manager can see the rfc as it the manager that is the admin.
                ],
            ],
        ];
    }
}
