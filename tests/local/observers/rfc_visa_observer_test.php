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

namespace customfield_sprogramme\local\observers;

use core_customfield\data_controller;
use customfield_sprogramme\local\persistent\notification;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\visa_manager;

/**
 *
 * RFC Visa observer test class.
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\observers\rfc_observer
 */
final class rfc_visa_observer_test extends \advanced_testcase {
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
        parent::setUp();
        $this->resetAfterTest();
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();

        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $this->course = $this->getDataGenerator()->create_course();
        $this->cfdata = $cfgenerator->add_instance_data($cfield, $this->course->id, 1);
        set_config('emailsenabled', true, 'customfield_sprogramme');
        set_config('approvalemail', 'admin@example.com,otheruser@example.com', 'customfield_sprogramme');
        set_config('defaultlang', 'en', 'customfield_sprogramme');
    }

    /**
     * Test that a notification is created when all visas are completed.
     */
    public function test_rfc_visa_notification_after_hod_approval_and_responsibles_done(): void {
        $generator = $this->getDataGenerator();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $creator = $generator->create_user(['username' => 'creator1']);

        $rfc = $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: '{}',
            usercreated: $creator->id,
        );

        $responsiblerolename = get_config('customfield_sprogramme', 'responsiblerolename');
        $generator->create_role(
            [
                'shortname' => $responsiblerolename,
                'name' => 'Responsible',
                'archetype' => 'editingteacher',
            ]
        );
        $responsible1 = $generator->create_and_enrol($this->course, $responsiblerolename, [
            'username' => 'responsible1',
            'email' => 'responsible1@example.com',
        ]);
        $responsible2 = $generator->create_and_enrol($this->course, $responsiblerolename, [
            'username' => 'responsible2',
            'email' => 'responsible2@example.com',
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
                'name' => 'Head of Department',
                'archetype' => 'teacher',
            ]
        );
        $generator->role_assign($hodroleid, $hoduser->id, \context_coursecat::instance($this->course->category));

        $visamanager = new visa_manager($rfc->id);
        $this->assertCount(0, notification::get_records(['notification' => 'rfc_visa_all_done']));

        $this->setUser($responsible1);
        $visamanager->reject_visa($responsible1->id, 'No');
        $this->assertCount(0, notification::get_records(['notification' => 'rfc_visa_all_done']));

        $this->setUser($hoduser);
        $visamanager->accept_visa($hoduser->id, 'Yes');
        $this->assertCount(0, notification::get_records(['notification' => 'rfc_visa_all_done']));

        $this->setUser($responsible2);
        $visamanager->reject_visa($responsible2->id, 'No');

        $notifications = notification::get_records(['notification' => 'rfc_visa_all_done']);
        $this->assertCount(2, $notifications);
        $recipients = array_map(fn($n) => $n->get('recipient'), $notifications);
        $this->assertContains('admin@example.com', $recipients);
        $this->assertContains('otheruser@example.com', $recipients);
    }
}
