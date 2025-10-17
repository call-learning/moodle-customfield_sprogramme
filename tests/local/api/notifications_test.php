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
        notifications::add_notification('rfc', $user1->id, $this->cfdata->get('id'));
        $this->assertCount(1, notification::get_records([]));

        $notification = notification::get_records([])[0];
        $this->assertEquals('rfc', $notification->get('notification'));
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
        notifications::add_notification('rfc', $user1->id, $this->cfdata->get('id'));
        $notifications = notification::get_records();
        $this->assertCount(2, $notifications);

        $notification = $notifications[0];
        $this->assertEquals('rfc', $notification->get('notification'));
        $this->assertEquals($this->cfdata->get('id'), $notification->get('datafieldid'));
        $this->assertEquals('recipient@example.com', $notification->get('recipient'));
        $this->assertEquals(notification::STATUS_PENDING, $notification->get('status'));

        $notification = $notifications[1];
        $this->assertEquals('rfc', $notification->get('notification'));
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
        notifications::add_notification('rfc', $user1->id, $this->cfdata->get('id'));
        notifications::add_notification('rfc', $user2->id, $this->cfdata->get('id'));
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
        notifications::add_notification('rfc', $user1->id, $this->cfdata->get('id'));
        notifications::add_notification('rfc', $user2->id, $this->cfdata->get('id'));
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_PENDING]));
        foreach (notification::get_records([]) as $notification) {
            $notification->send();
        }
        $emails = $emailsink->get_messages();
        $this->assertCount(0, $emails);
        $this->assertCount(0, notification::get_records(['status' => notification::STATUS_SEND]));
        $this->assertCount(2, notification::get_records(['status' => notification::STATUS_PENDING]));
    }
}
