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

use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_module;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\test\testcase_helper_trait;

/**
 * Functional test for programme manager class
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\local\programme_manager
 */
final class programme_manager_test extends \advanced_testcase {
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
     * Data provider for test_get_column_structure
     *
     * @return array
     */
    public static function get_column_structure_data(): array {
        // There is something wrong here with get_column_structure as canedit is always true.
        return [
            'Teacher 1 with rfc created' => [
                'username' => 'teacher1',
                'createrfc' => true,
                'expected' => [
                    'canedit' => true,
                ],
            ],
            'Teacher 1 without rfc created' => [
                'username' => 'teacher1',
                'createrfc' => false,
                'expected' => [
                    'canedit' => true,
                ],
            ],
            'Teacher 2 with rfc created' => [
                'username' => 'teacher1',
                'createrfc' => true,
                'expected' => [
                    'canedit' => true,
                ],
            ],
            'Teacher 2 without rfc created' => [
                'username' => 'teacher1',
                'createrfc' => false,
                'expected' => [
                    'canedit' => true,
                ],
            ],
            'Manager with rfc created' => [
                'username' => 'teacher1',
                'createrfc' => true,
                'expected' => [
                    'canedit' => true,
                ],
            ],
            'Manager without rfc created' => [
                'username' => 'teacher1',
                'createrfc' => false,
                'expected' => [
                    'canedit' => true,
                ],
            ],
        ];
    }

    /**
     * Data provider for test_has_history
     *
     * @return array
     */
    public static function has_history_data(): array {
        return [
            'No RFC' => [
                'rfcstatus' => -1,
                'expected' => false,
            ],
            'Cancelled RFC' => [
                'rfcstatus' => sprogramme_rfc::RFC_CANCELLED,
                'expected' => false,
            ],
            'Requested RFC' => [
                'rfcstatus' => sprogramme_rfc::RFC_REQUESTED,
                'expected' => false,
            ],
            'Submitted RFC' => [
                'rfcstatus' => sprogramme_rfc::RFC_SUBMITTED,
                'expected' => false,
            ],
            'Accepted RFC' => [
                'rfcstatus' => sprogramme_rfc::RFC_ACCEPTED,
                'expected' => true,
            ],
            'Rejected RFC' => [
                'rfcstatus' => sprogramme_rfc::RFC_REJECTED,
                'expected' => true,
            ],
        ];
    }

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
     * Test the programme manager class.
     */
    public function test_get_data(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $this->assertEmpty($pm->get_data());
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $data = $pm->get_data();
        $this->assert_programme_data_equals($this->sampleprogrammedata[0], $data);
    }

    /**
     * Test set data method
     */
    public function test_set_data(): void {
        $pm = new programme_manager($this->cfdata->get('id'));
        $this->assertEmpty($pm->get_data());
        $pm->set_data($this->sampleprogrammedata[0]);
        $this->assertCount(1, sprogramme_module::get_records()); // We should have 1 module.
        $this->assertCount(2, sprogramme::get_records()); // We should have 2 rows.
        $data = $pm->get_data();
        $this->assert_programme_data_equals($this->sampleprogrammedata[0], $data);
        // Update the programme with new data.
        $updateddata = $pm->get_data();
        $updateddata[0]['rows'][0]['deleted'] = true; // Delete the first row.
        $updateddata[0]['modulename'] = 'Updated Module Name';
        $updateddata[0]['modulesortorder'] = 2; // Change the sort.

        // We need to set the modulesortorder to match the existing modules.
        $pm->set_data($updateddata);
        $data = $pm->get_data();
        // Unset the deleted row from the expected data.
        array_shift($updateddata[0]['rows']);
        $this->assert_programme_data_equals($updateddata, $data);
        $this->assertCount(1, sprogramme::get_records()); // We should have 1 row.
    }

    /**
     * Test set data method
     *
     * @param string $username The username to test.
     * @param bool $createrfc Whether to create an rfc before testing.
     * @param array $expected The expected results.
     *
     * @dataProvider get_column_structure_data
     */
    public function test_get_column_structure(
        string $username,
        bool $createrfc,
        array $expected
    ): void {
        $pm = new programme_manager($this->cfdata->get('id'));
        $users['teacher1'] = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $users['teacher2'] = $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');
        $users['manager'] = $this->getDataGenerator()->create_and_enrol($this->course, 'manager');

        $this->setUser($users[$username]);
        if ($createrfc) {
            // Create a rfc to test the canaddrfc flag.
            $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
            $pgenerator->create_rfc(
                $this->cfdata->get('id'),
                userid: $users['teacher1']->id,
                type: sprogramme_rfc::RFC_SUBMITTED,
                snapshot: json_encode($this->sampleprogrammedata[0])
            );
        }
        $columns = $pm->get_column_structure();
        $this->assertCount(13, $columns);
        $this->assertEquals([
            'dd_rse' => 'select',
            'intitule_seance' => 'text',
            'cm' => 'float',
            'td' => 'float',
            'tp' => 'float',
            'tpa' => 'float',
            'tc' => 'int',
            'aas' => 'float',
            'fmp' => 'float',
            'perso_av' => 'float',
            'perso_ap' => 'float',
            'consignes' => 'text',
            'supports' => 'text',
        ], array_column($columns, 'field', 'column'));// We should have 5 columns.
        foreach (array_keys($expected) as $flag) {
            $this->assertEquals($expected[$flag], $columns[0][$flag], "Flag {$flag} does not match");
        }

    }

    /**
     * Test if we can edit
     */
    public function test_can_edit(): void {
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
        $programmemanager = new programme_manager($this->cfdata->get('id'));
        $this->setUser($user1);
        $this->assertFalse($programmemanager->can_edit());
        $this->setUser($teacher1);
        $this->assertFalse($programmemanager->can_edit()); // We submitted a rfc, we cannot edit now.
        $this->setAdminUser();
        // Accept the rfc.
        $rfcmanager->accept($teacher2->id); // Programme data is present in the snapshot.
        $this->setUser($teacher2);
        $this->assertTrue($programmemanager->can_edit()); // Now we can edit.
    }

    /**
     * Test if we can validate data
     */
    public function test_validate_data(): void {
        $pm = new programme_manager($this->cfdata->get('id'));
        // Valid data.
        $errors = $pm->validate_data($this->sampleprogrammedata[0]);
        $this->assertEmpty($errors);
        // Invalid data: missing module name.
        $invaliddata = $this->sampleprogrammedata[0];
        $invaliddata[0]['rows'][0]['cells'][2]['value'] = '-5A'; // Invalid cm value.
        $errors = $pm->validate_data($invaliddata);
        $this->assertNotEmpty($errors);
        $this->assertEquals(
            [
                'module' => 'Test Module 1',
                'row' => 0,
                'column' => 'CM',
                'error' => 'Invalid value for column CM: -5A',
            ],
            $errors[0]
        );
    }

    /**
     * Test if we have history (RFCs)
     *
     * @param int $rfcstatus The status of the rfc to create, -1 for no rfc.
     * @param bool $expected The expected result.
     *
     * @dataProvider has_history_data
     */
    public function test_has_history(int $rfcstatus, bool $expected): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $pm = new programme_manager($this->cfdata->get('id'));
        if ($rfcstatus != -1) {
            $pgenerator->create_rfc(
                $this->cfdata->get('id'),
                userid: $teacher1->id,
                type: $rfcstatus,
                snapshot: json_encode($this->sampleprogrammedata[0])
            );
        }
        $this->assertEquals($expected, $pm->has_history());
    }

    /**
     * Test if we have data
     */
    public function test_has_data(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $this->assertFalse($pm->has_data());
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $this->assertTrue($pm->has_data());
    }

    /**
     * Test if we have data
     */
    public function test_delete_programme(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $this->assertTrue($pm->has_data());
        $pm->delete_programme();
        $this->assertFalse($pm->has_data());
        $this->assertCount(0, sprogramme::get_records());
        $this->assertCount(0, sprogramme_module::get_records());
    }

    /**
     * Test update sort order
     */
    public function test_update_sortorder(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $data = $pm->get_data();
        $originalrowids = array_column($data[0]['rows'], 'id');
        $expectedroworder = array_combine($originalrowids, [0, 1]); // Original sort order.
        $this->assertEquals($expectedroworder, array_column($data[0]['rows'], 'sortorder', 'id'));
        // Now update the sort order.
        $data[0]['rows'][0]['sortorder'] = 5;
        $pm->set_data($data);
        $data = $pm->get_data();
        $expectedroworder = array_combine($originalrowids, [5, 1]); // Sort order not updated yet.
        $this->assertEquals($expectedroworder, array_column($data[0]['rows'], 'sortorder', 'id'));
        $pm->update_sort_order('row', 0, $data[0]['rows'][1]['id'], $data[0]['rows'][1]['id']);
        $data = $pm->get_data();
        $originalrowids = array_column($data[0]['rows'], 'id');
        $expectedroworder = array_combine($originalrowids, [1, 5]);
        $this->assertEquals($expectedroworder, array_column($data[0]['rows'], 'sortorder', 'id'));
    }

    /**
     * Test get csv data
     */
    public function test_get_csv_data(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $csvdata = $pm->get_csv_data();
        // We should have 2 rows + header.
        $rows = explode("\n", $csvdata);
        $rows = array_filter($rows, fn($value) => !empty(trim($value)));
        $rows = array_map('str_getcsv', $rows);
        $this->assertCount(3, $rows);
        $expectedrows = [
            [
                "module",
                "dd_rse",
                "intitule_seance",
                "cm",
                "td",
                "tp",
                "tpa",
                "tc",
                "aas",
                "fmp",
                "perso_av",
                "perso_ap",
                "consignes",
                "supports",
                "disciplines_1",
                "%_disciplines_1",
                "disciplines_2",
                "%_disciplines_2",
                "disciplines_3",
                "%_disciplines_3",
                "competencies_1",
                "%_competencies_1",
                "competencies_2",
                "%_competencies_2",
                "competencies_3",
                "%_competencies_3",
                "competencies_4",
                "%_competencies_4",
            ],
            [
                "Test Module 1",
                "",
                "Seance 1",
                "15",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
            ],
            [
                "Test Module 1",
                "Sans lien avec DD/RSE",
                "Seance 2",
                "10.5",
                "46",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
            ],
        ];

        $this->assertEquals($expectedrows, $rows);
    }

    /**
     * Test has protected data changes
     */
    public function test_has_protected_data_changes(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        // No changes.
        $data = $pm->get_data();
        $this->assertFalse($pm->has_protected_data_changes($data));
        // Change a non protected field (dd_rse).
        $data[0]['rows'][0]['cells'][0]['value'] = 'NewRSE';
        $this->assertFalse($pm->has_protected_data_changes($data));
        // Change a protected field (cm).
        $data[0]['rows'][0]['cells'][2]['value'] = 20.0;
        $this->assertTrue($pm->has_protected_data_changes($data));
    }

    /**
     * Test get sums
     */
    public function test_get_sums(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $sums = $pm->get_sums();
        $sumwithlabels = array_column($sums, 'sum', 'column');
        $this->assertEquals([
            'cm' => 25.5,
            'td' => 46.0,
            'tp' => 0,
            'tpa' => 0,
            'tc' => 0,
            'aas' => 0,
            'fmp' => 0,
            'perso_av' => 0,
            'perso_ap' => 0,
        ], $sumwithlabels);
    }

    /**
     * Test get numeric columns
     */
    public function test_get_numeric_columns(): void {
        $numericcolumns = programme_manager::get_numeric_columns();
        $this->assertEquals(
            [
                'cm', 'td', 'tp', 'tpa', 'tc', 'aas', 'fmp', 'perso_av', 'perso_ap'
            ], array_column($numericcolumns, 'column')
        );
    }

    /**
     * Test get column totals
     */
    public function test_get_column_totals(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $pm = new programme_manager($this->cfdata->get('id'));
        $pgenerator->create_programme(
            $this->cfdata->get('id'),
            $this->sampleprogrammedata[0]
        );
        $modules = $pm->get_data(false);
        $columns = $pm->get_column_structure();
        $totals = $pm->get_column_totals($modules, $columns);
        $totalswithlabels = array_column($totals, 'sum', 'column');
        $this->assertEquals([
            'cm' => 25.5,
            'td' => 46.0,
            'tp' => 0,
            'tpa' => 0,
            'tc' => 0,
            'aas' => 0,
            'fmp' => 0,
            'perso_av' => 0,
            'perso_ap' => 0,
            'dd_rse' => 0,
            'intitule_seance' => 0,
            'consignes' => 0,
            'supports' => 0,
        ], $totalswithlabels);
    }

    /**
     * Test get history
     */
    public function test_get_history(): void {
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $teacher1 = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $pm = new programme_manager($this->cfdata->get('id'));
        // No history.
        $this->assertFalse($pm->has_history());
        $this->setUser($teacher1);
        $rfc1 = $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher1->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($this->sampleprogrammedata[0])
        );
        // Now we should have history.
        $this->assertFalse($pm->has_history());
        $history = $pm->get_history($rfc1->id);
        $this->assertCount(1, $history['rfcs']);
        $this->assertCount(1, $history['modules']);
        $this->assert_programme_data_equals($this->sampleprogrammedata[0], $history['modules']);
        // Create another rfc.
        $modifieddata = $this->sampleprogrammedata[0];
        $modifieddata[0]['rows'][0]['dd_rse'] = 'New RSE Value';
        $rfc2 = $pgenerator->create_rfc(
            $this->cfdata->get('id'),
            userid: $teacher1->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($modifieddata)
        );
        // Now we should have history.
        $history = $pm->get_history($rfc2->id);
        $this->assertCount(1, $history['rfcs']);
        $this->assertCount(1, $history['modules']);
        $this->assert_programme_data_equals($modifieddata, $history['modules']);
        // Test with rfcid.
        $history = $pm->get_history($rfc1->id);
        $this->assertCount(1, $history['rfcs']);
        $this->assertCount(1, $history['modules']);
        $this->assert_programme_data_equals($this->sampleprogrammedata[0], $history['modules']);
    }
}
