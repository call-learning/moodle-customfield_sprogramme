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

namespace customfield_sprogramme;
use core_customfield_test_instance_form;
use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_comp;
use customfield_sprogramme\local\persistent\sprogramme_complist;
use customfield_sprogramme\local\persistent\sprogramme_disc;
use customfield_sprogramme\local\persistent\sprogramme_disclist;
use customfield_sprogramme\local\persistent\sprogramme_module;

/**
 * Functional test for customfield_sprogramme plugin
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\field_controller
 * @covers \customfield_sprogramme\data_controller
 */
final class plugin_test extends \advanced_testcase {
    /** @var \stdClass[]  */
    private $courses = [];
    /** @var \core_customfield\category_controller */
    private $cfcat;
    /** @var \core_customfield\field_controller[] */
    private $cfields;
    /** @var \core_customfield\data_controller[] */
    private $cfdata;

    /**
     * Tests set up.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $this->cfcat = $cfgenerator->create_category();

        $this->cfields[1] = $cfgenerator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $this->cfields[2] = $cfgenerator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield2', 'type' => 'sprogramme',
            'configdata' => ['required' => 1]]
        );
        $this->cfields[3] = $cfgenerator->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield3', 'type' => 'sprogramme',
            'configdata' => ['enabledbydefault' => 1]]
        );

        $this->courses[1] = $this->getDataGenerator()->create_course();
        $this->courses[2] = $this->getDataGenerator()->create_course();
        $this->courses[3] = $this->getDataGenerator()->create_course();

        $this->cfdata[1] = $cfgenerator->add_instance_data($this->cfields[1], $this->courses[1]->id, 1);
        $this->cfdata[2] = $cfgenerator->add_instance_data($this->cfields[2], $this->courses[2]->id, 1);

        $this->setUser($this->getDataGenerator()->create_user());
    }

    /**
     * Test for initialising field and data controllers
     */
    public function test_initialise(): void {
        $f = \core_customfield\field_controller::create($this->cfields[1]->get('id'));
        $this->assertTrue($f instanceof \customfield_sprogramme\field_controller);

        $f = \core_customfield\field_controller::create(0, (object)['type' => 'sprogramme'], $this->cfcat);
        $this->assertTrue($f instanceof \customfield_sprogramme\field_controller);

        $d = \core_customfield\data_controller::create($this->cfdata[1]->get('id'));
        $this->assertTrue($d instanceof \customfield_sprogramme\data_controller);

        $d = \core_customfield\data_controller::create(0, null, $this->cfields[1]);
        $this->assertTrue($d instanceof \customfield_sprogramme\data_controller);
    }

    /**
     * Test for configuration form functions
     *
     * Create a configuration form and submit it with the same values as in the field
     */
    public function test_config_form(): void {
        $this->setAdminUser();
        $submitdata = (array)$this->cfields[1]->to_record();
        $submitdata['configdata'] = $this->cfields[1]->get('configdata');

        $submitdata = \core_customfield\field_config_form::mock_ajax_submit($submitdata);
        $form = new \core_customfield\field_config_form(
            null,
            null,
            'post',
            '',
            null,
            true,
            $submitdata,
            true
        );
        $form->set_data_for_dynamic_submission();
        $this->assertTrue($form->is_validated());
        $form->process_dynamic_submission();
        $this->assertTrue($form->is_validated());
    }

    /**
     * Test for instance form functions
     */
    public function test_instance_form(): void {
        global $CFG;
        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        // First try to submit without required field.
        $submitdata = (array)$this->courses[1];
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertTrue($form->is_validated());

        // Now with required field.
        $submitdata['customfield_myfield2'] = 0;
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $this->assertTrue($form->is_validated());

        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);
        $data = \core_customfield\handler::get_handler('core_course', 'course')->get_instance_data($this->courses[1]->id);
        // Custom field 2 is set to 0.
        // Custom field 3 is enabled by default so it is set to 1.
        $this->assertEquals([1, 0, 1], array_values(array_map(fn($d) => $d->get_value(), $data)));
    }

    /**
     * Test for data_controller::get_value and export_value
     */
    public function test_get_export_value(): void {
        $this->assertEquals(1, $this->cfdata[1]->get_value());
        $this->assertStringContainsString('customfield-sprogramme', $this->cfdata[1]->export_value());

        // Field without data.
        $d = \core_customfield\data_controller::create(0, null, $this->cfields[2]);
        $this->assertEquals(0, $d->get_value()); // Not enabled.
        $this->assertNull($d->export_value());

        // Field without data that is checked by default.
        $d = \core_customfield\data_controller::create(0, null, $this->cfields[3]);
        $this->assertEquals(1, $d->get_value()); // Enabled but no value.
        $this->assertNull($d->export_value());
    }

    /**
     * Deleting fields and data
     * @runInSeparateProcess
     */
    public function test_delete(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        $sampledata = get_sample_programme_data();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pgenerator->create_programme(
            $this->cfdata[1]->get('id'),
            $sampledata[0]
        );

        $this->assertNotEmpty($DB->get_records('customfield_field'));
        $this->assertNotEmpty($DB->get_records('customfield_data'));
        $this->assertNotEmpty($DB->get_records('customfield_sprogramme'));
        $this->assertNotEmpty($DB->get_records('customfield_sprogramme_module'));
        $this->assertNotEmpty($DB->get_records('customfield_sprogramme_disc'));
        $this->assertNotEmpty($DB->get_records('customfield_sprogramme_competencies'));

        $this->cfcat->get_handler()->delete_all();
        $this->assertEmpty($DB->get_records('customfield_field'));
        $this->assertEmpty($DB->get_records('customfield_data'));
        $this->assertEmpty($DB->get_records('customfield_sprogramme'));
        $this->assertEmpty($DB->get_records('customfield_sprogramme_module'));
        $this->assertEmpty($DB->get_records('customfield_sprogramme_disc'));
        $this->assertEmpty($DB->get_records('customfield_sprogramme_competencies'));
    }

    /**
     * Test embedded file backup and restore.
     *
     * @covers \customfield_textarea\data_controller::backup_define_structure
     * @covers \customfield_textarea\data_controller::backup_restore_structure
     */
    public function test_backup_and_restore(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');
        $sampledata = get_sample_programme_data();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        // Set the required field and submit.
        // First try to submit without required field.
        $submitdata = (array)$this->courses[1];
        $submitdata['customfield_myfield1'] = 1;
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $submitdata['customfield_myfield2'] = 0;
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form(
            'POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]
        );
        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);

        // Add programme data.
        $pgenerator->create_programme(
            $this->cfdata[1]->get('id'),
            $sampledata[0]
        );
        // Check values before backup.
        $this->assertCount(2, sprogramme::get_records());
        $this->assertCount(1, sprogramme_module::get_records());
        $this->assertCount(1, sprogramme_comp::get_records());
        $this->assertCount(2, sprogramme_disc::get_records());
        $existingcompscount = sprogramme_complist::count_records();
        $existingdiscscount = sprogramme_disclist::count_records();

        // Backup and restore the course.
        $backupid = $this->backup($this->courses[1]);
        $newcourseid = $this->restore($backupid, $this->courses[1], '_copy');
        $newcf1data = $DB->get_record(
            'customfield_data',
            ['instanceid' => $newcourseid, 'fieldid' => $this->cfields[1]->get('id')]
        );
        $this->assertNotEmpty($newcf1data);
        $this->assertNotEmpty($newcf1data->intvalue);
        $newcf2data = $DB->get_record(
            'customfield_data',
            ['instanceid' => $newcourseid, 'fieldid' => $this->cfields[2]->get('id')]
        );
        $this->assertNotEmpty($newcf2data);
        $this->assertEmpty($newcf2data->intvalue);

        $this->assertCount(4, sprogramme::get_records());
        $this->assertCount(2, sprogramme_module::get_records());
        $this->assertCount(2, sprogramme_comp::get_records());
        $this->assertCount(4, sprogramme_disc::get_records());

        $this->assertCount(2, sprogramme::get_records(['datafieldid' => $newcf1data->id]));
        $this->assertCount(1, sprogramme_module::get_records(['datafieldid' => $newcf1data->id]));
        $this->assertCount(1, sprogramme_comp::get_records_select(
            'pid IN (SELECT id FROM {customfield_sprogramme} WHERE datafieldid = :cid)',
            ['cid' => $newcf1data->id]
        ));
        $this->assertCount(2, sprogramme_disc::get_records_select(
            'pid IN (SELECT id FROM {customfield_sprogramme} WHERE datafieldid = :did)',
            ['did' => $newcf1data->id]
        ));
        // Competency and discipline should not be duplicated.
        $this->assertEquals($existingcompscount, sprogramme_complist::count_records());
        $this->assertEquals($existingdiscscount, sprogramme_disclist::count_records());
    }

    /**
     * Backs a course up to temp directory.
     *
     * @param \stdClass $course Course object to backup
     * @return string ID of backup
     */
    protected function backup($course): string {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

        // Turn off file logging, otherwise it can't delete the file (Windows).
        $CFG->backup_file_logger_level = \backup::LOG_NONE;

        // Do backup with default settings. MODE_IMPORT means it will just
        // create the directory and not zip it.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->get_plan()->get_setting('logs')->set_value(true);
        $backupid = $bc->get_backupid();

        $bc->execute_plan();
        $bc->destroy();
        return $backupid;
    }

    /**
     * Restores a course from temp directory.
     *
     * @param string $backupid Backup id
     * @param \stdClass $course Original course object
     * @param string $suffix Suffix to add after original course shortname and fullname
     * @return int New course id
     * @throws \restore_controller_exception
     */
    protected function restore(string $backupid, $course, string $suffix): int {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Do restore to new course with default settings.
        $newcourseid = \restore_dbops::create_new_course(
            $course->fullname . $suffix,
            $course->shortname . $suffix,
            $course->category
        );
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('logs')->set_value(true);
        $rc->get_plan()->get_setting('users')->set_value(true);

        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}
