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
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\rfc_manager;
use customfield_sprogramme\task\notifications;
use customfield_sprogramme\test\testcase_helper_trait;

/**
 *
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\observers\rfc_observer
 */
final class rfc_observer_test extends \advanced_testcase {
    use testcase_helper_trait;

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
        $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', [
            'username' => 'teacher1',
            'email' => 'teacher1@example.com',
            'firstname' => 'Teacher',
            'lastname' => 'One',
        ]);
        set_config('emailsenabled', true, 'customfield_sprogramme');
        set_config('approvalemail', 'admin@example.com,otheruser@example.com', 'customfield_sprogramme');
    }

    /**
     * Test that an email is sent when an RFC is submitted
     */
    public function test_rfc_submitted_email_sent(): void {
        global $DB;
        $this->resetAfterTest();

        $emailsink = $this->redirectEmails();
        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $teacher = $DB->get_record('user', ['username' => 'teacher1']);
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            usercreated: $teacher->id,
        );
        $rfcmanager->submit($teacher->id);
        // Execute the cron.
        ob_start();
        \core\cron::setup_user();
        $cron = new notifications();
        $cron->execute();
        ob_end_clean();

        $emails = $emailsink->get_messages();
        $this->assertCount(2, $emails);
        $emailsto = array_map(fn($email) => $email->to, $emails);
        $this->assertContains('admin@example.com', $emailsto);
        $this->assertContains('otheruser@example.com', $emailsto);
        $email = reset($emails);
        $this->assertEquals('[Syllabus] Request for programme change for: tc_1 - Test course 1', $email->subject);
    }

    /**
     * Test that an email is sent when an RFC is accepted
     */
    public function test_rfc_accepted_email_sent(): void {
        global $DB;
        $this->resetAfterTest();

        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $teacher = $DB->get_record('user', ['username' => 'teacher1']);
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode([
                [
                    'moduleid' => -1,
                    'modulesortorder' => 0,
                    'modulename' => 'Test Module 1',
                    'deleted' => false,
                    'rows' => [],
                ],
            ]),
            usercreated: $teacher->id,
        );

        // Prepare the data for the context.
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

        $emailsink = $this->redirectEmails();
        // Accept the RFC.
        $this->setAdminUser();
        $rfcmanager->accept(
            $teacher->id,
        );
        // Execute the cron.
        ob_start();
        \core\cron::setup_user();
        $cron = new notifications();
        $cron->execute();
        ob_end_clean();

        $emails = $emailsink->get_messages();
        // No email should be sent on approval.
        $this->assertCount(6, $emails);
        $emailsto = array_map(fn($email) => $email->to, $emails);
        $this->assertContains('admin@example.com', $emailsto);
        $this->assertContains('otheruser@example.com', $emailsto);
        $this->assertContains('teacher1@example.com', $emailsto);
        $this->assertContains('responsible1@example.com', $emailsto);
        $this->assertContains('responsible2@example.com', $emailsto);
        $this->assertContains('headofdepartment@example.com', $emailsto);
        $email = reset($emails);
        $this->assertEquals(
            '[Syllabus] Programme change validated for UC: tc_1 - Test course 1',
            $email->subject
        );
        $emailwithoutlinebreaks = str_replace(["\r", "\n"], ' ', $email->body);
        $emailwithoutlinebreaks = preg_replace('/\s+/', ' ', $emailwithoutlinebreaks);
        $this->assertStringContainsString('UC Responsible(s)', $emailwithoutlinebreaks);
        $this->assertStringContainsString('Head of Department', $emailwithoutlinebreaks);
        $this->assertStringContainsString('Responsible One', $emailwithoutlinebreaks);
        $this->assertStringContainsString('Responsible Two', $emailwithoutlinebreaks);
        $this->assertStringContainsString('Requester: Teacher One', $emailwithoutlinebreaks);
        $this->assertStringContainsString('Department: DSPB', $emailwithoutlinebreaks);
    }

    /**
     * Test that an email is sent when an RFC is rejected.
     */
    public function test_rfc_rejected_email_sent(): void {
        global $DB;
        $this->resetAfterTest();

        $rfcmanager = new rfc_manager($this->cfdata->get('id'));
        $teacher = $DB->get_record('user', ['username' => 'teacher1']);
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode([
                [
                    'moduleid' => -1,
                    'modulesortorder' => 0,
                    'modulename' => 'Test Module 1',
                    'deleted' => false,
                    'rows' => [],
                ],
            ]),
            usercreated: $teacher->id,
        );

        $emailsink = $this->redirectEmails();
        $this->setAdminUser();
        $rfcmanager->reject($teacher->id);

        ob_start();
        \core\cron::setup_user();
        $cron = new notifications();
        $cron->execute();
        ob_end_clean();

        $emails = $emailsink->get_messages();
        $this->assertCount(3, $emails);
        $emailsto = array_map(fn($email) => $email->to, $emails);
        $this->assertContains('admin@example.com', $emailsto);
        $this->assertContains('otheruser@example.com', $emailsto);
        $this->assertContains('teacher1@example.com', $emailsto);
        $email = reset($emails);
        $this->assertEquals(
            '[Syllabus] Programme change not approved for UC: tc_1 - Test course 1',
            $email->subject
        );
    }
}
