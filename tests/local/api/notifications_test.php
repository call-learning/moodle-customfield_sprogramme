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

namespace customfield_sprogramme\local\api;

use core_customfield\data_controller;
use customfield_sprogramme\local\persistent\notification;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\test\testcase_helper_trait;

/**
 * Functional test for programme manager class
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\api\notifications
 */
final class notifications_test extends \advanced_testcase {
    use testcase_helper_trait;

    /**
     * Sample programme data
     *
     * @var array
     */
    protected array $sampleprogrammedata;

    /**
     * Custom field data
     *
     * @var data_controller $cfdata ;
     */
    protected data_controller $cfdata;

    /**
     * Course data
     *
     * @var \stdClass $course ;
     */
    protected \stdClass $course;

    /**
     * Setup test environment
     */
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        $this->sampleprogrammedata = get_sample_programme_data();

        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();

        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $course = $this->getDataGenerator()->create_course();
        $this->cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);
        $this->course = $course;
        set_config('emailsenabled', true, 'customfield_sprogramme');
    }

    /**
     * Test adding a notification
     */
    public function test_add_notification(): void {
        // Settings.
        $user1 = $this->getDataGenerator()->create_user('user1');
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user1->id,
        );
        notifications::add_notification('rfc_submitted', $user1->id, $this->cfdata->get('id'));
        $this->assertCount(1, notification::get_records([]));

        $notification = notification::get_records([])[0];
        $this->assertEquals('rfc_submitted', $notification->get('notification'));
        $this->assertEquals($this->cfdata->get('id'), $notification->get('datafieldid'));
        $this->assertEquals('admin@example.com', $notification->get('recipient')); // Default admin email.
        $this->assertEquals(notification::STATUS_PENDING, $notification->get('status'));
        $this->assertStringContainsString(
            '[Syllabus] Request for programme change for: tc_1 - Test course 1',
            $notification->get('subject')
        );
        $this->assertStringContainsString(
            'A change request has been submitted for the programme of the following course: tc_1 - Test course 1.',
            $notification->get('body')
        );
    }

    /**
     * Test adding a notification with approvalemail setting
     */
    public function test_add_notification_approvalemail(): void {
        // Settings.
        set_config('approvalemail', 'recipient@example.com,recipient2@example.com', 'customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_user('user1');
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user1->id,
        );
        notifications::add_notification('rfc_submitted', $user1->id, $this->cfdata->get('id'));
        $notifications = notification::get_records();
        $this->assertCount(2, $notifications);

        $notification = $notifications[0];
        $this->assertEquals('rfc_submitted', $notification->get('notification'));
        $this->assertEquals($this->cfdata->get('id'), $notification->get('datafieldid'));
        $this->assertEquals('recipient@example.com', $notification->get('recipient'));
        $this->assertEquals(notification::STATUS_PENDING, $notification->get('status'));

        $notification = $notifications[1];
        $this->assertEquals('rfc_submitted', $notification->get('notification'));
        $this->assertEquals($this->cfdata->get('id'), $notification->get('datafieldid'));
        $this->assertEquals('recipient2@example.com', $notification->get('recipient'));
        $this->assertEquals(notification::STATUS_PENDING, $notification->get('status'));
    }

    /**
     * Test sending notifications
     */
    public function test_send_notifications(): void {
        $emailsink = $this->redirectEmails();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_user('user1');
        $user2 = $this->getDataGenerator()->create_user('user2');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user1->id,
        );
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_CANCELLED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user2->id,
        );
        notifications::add_notification('rfc_submitted', $user1->id, $this->cfdata->get('id'));
        notifications::add_notification('rfc_submitted', $user2->id, $this->cfdata->get('id'));
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_PENDING]));
        foreach (notification::get_records([]) as $notification) {
            $notification->send();
        }
        $emails = $emailsink->get_messages();
        $this->assertCount(2, $emails);
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_SEND]));
        $this->assertStringContainsString(
            '[Syllabus] Request for programme change for: tc_1 - Test course 1',
            $emails[0]->subject
        );
        $this->assertStringContainsString(
            'A change request has been submitted for the programme',
            $emails[0]->body
        );
        $this->assertStringContainsString(
            "Once this agreement has been communicated, the director of training will\r\n"
            . "proceed with the final validation and update the overall educational\r\n"
            . "framework.\r\n",
            $emails[0]->body
        );
    }

    /**
     * Test sending notifications when emails are disabled
     */
    public function test_send_notifications_disabled(): void {
        set_config('emailsenabled', false, 'customfield_sprogramme');
        $emailsink = $this->redirectEmails();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $user1 = $this->getDataGenerator()->create_user('user1');
        $user2 = $this->getDataGenerator()->create_user('user2');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user1->id,
        );
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_CANCELLED,
            snapshot: json_encode($this->sampleprogrammedata[0]),
            usercreated: $user2->id,
        );
        notifications::add_notification('rfc_submitted', $user1->id, $this->cfdata->get('id'));
        notifications::add_notification('rfc_submitted', $user2->id, $this->cfdata->get('id'));
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_PENDING]));
        foreach (notification::get_records([]) as $notification) {
            $notification->send();
        }
        $emails = $emailsink->get_messages();
        $this->assertCount(0, $emails);
        $this->assertCount(0, notification::get_records(['status' => notification::STATUS_SEND]));
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_PENDING]));
    }

    /**
     * Test adding global context
     */
    public function test_add_global_context(): void {
        $generator = $this->getDataGenerator();

        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        // If the local_envasyllabus plugin is not installed, the department field will not be presents, so we change the default
        // value to a new field.
        $cfielddept = $cfgenerator->create_field(
            [
                'categoryid' =>
                    $this->cfdata->get_field()->get_category()->get('id'),
                'shortname' => 'newdept',
                'type' => 'text',
            ]
        );
        $cfgenerator->add_instance_data($cfielddept, $this->course->id, 'DSPB');
        set_config('departmentcustomfieldname', 'newdept', 'customfield_sprogramme');

        // Responsible and department head roles are required for the context, so we create them and enrol
        // some users with these roles.
        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $generator->create_role(
            [
                'shortname' => $responsiblerolename,
                'name' => 'Responsible',
                'archetype' => 'editingteacher',
            ]
        );
        $generator->create_and_enrol($this->course, $responsiblerolename, [
            'username' => 'responsible1',
            'email' => 'responsible1@example.com',
            'firstname' => 'Responsible',
            'lastname' => 'One',
        ]);
        $generator->create_and_enrol($this->course, $responsiblerolename, [
            'username' => 'responsible2',
            'email' => 'responsible2@example.com',
            'firstname' => 'Responsible',
            'lastname' => 'Two',
        ]);

        $hoduser = $generator->create_user([
            'username' => 'headofdepartment1',
            'email' => 'headofdepartment@example.com',
            'firstname' => 'Head of',
            'lastname' => 'Department',
        ]);
        $departmentheadrolename = get_config('customfield_sprogramme', 'departmentheadrolename');
        $hodroleid = $generator->create_role(
            [
                'shortname' => $departmentheadrolename,
                'name' => 'Responsible',
                'archetype' => 'teacher',
            ]
        );
        $generator->role_assign($hodroleid, $hoduser->id, \context_coursecat::instance($this->course->category));

        $method = new \ReflectionMethod(notifications::class, 'add_global_context');
        $method->setAccessible(true);
        $user = $generator->create_user([
            'username' => 'user1',
            'email' => 'user1@example.com',
            'firstname' => 'User',
            'lastname' => '1',
        ]);
        $context = $method->invoke(
            null,
            ['usercreated' => $user->id],
            $this->cfdata->get('id'),
            $user->id
        );
        $this->assertArrayHasKey('programmelink', $context);
        $this->assertArrayHasKey('coursename', $context);
        $this->assertArrayHasKey('department', $context);
        $this->assertArrayHasKey('responsibles', $context);
        $this->assertArrayHasKey('requester', $context);
        $this->assertStringContainsString(
            '/local/envasyllabus/syllabuspage.php?id=' . $this->cfdata->get('instanceid'),
            $context['programmelink']
        );
        $this->assertEquals('tc_1 - Test course 1', $context['coursename']);
        $this->assertEquals('DSPB', $context['department']);
        $this->assertStringContainsString('Responsible One', $context['responsibles']);
        $this->assertStringContainsString('Responsible Two', $context['responsibles']);
        $this->assertStringContainsString('Head of Department', $context['responsibles']);
        $this->assertEquals('User 1', $context['requester']);
    }
}
