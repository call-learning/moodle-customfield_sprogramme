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

namespace customfield_sprogramme\local\importer;

use customfield_sprogramme\local\persistent\sprogramme;
use customfield_sprogramme\local\persistent\sprogramme_comp;
use customfield_sprogramme\local\persistent\sprogramme_disc;

/**
 * Tests for Programme customfield
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr> / SAS CALL Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class programme_importer_test extends \advanced_testcase {
    /**
     * @var int $cfdid The custom field instance data.
     */
    protected int $cfdid;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $customfieldgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $options = [
            'component' => 'core_course',
            'area'      => 'course',
            'itemid'    => 0,
            'contextid' => \context_system::instance()->id,
        ];
        $category = $customfieldgenerator->create_category($options);
        $cf = $customfieldgenerator->create_field(
            [
                'name' => 'Programme',
                'shortname' => 'programme',
                'type' => 'sprogramme',
                'contextlevel' => CONTEXT_COURSE,
                'categoryid' => $category->get('id'),
            ]
        );
        $course = $this->getDataGenerator()->create_course('Test course');
        $cfd = $customfieldgenerator->add_instance_data($cf, $course->id, 1);
        $this->cfdid = $cfd->get('id');
    }

    /**
     * Test the importer with a sample CSV file.
     *
     * @covers \customfield_sprogramme\local\importer\programme_importer::import
     */
    function test_importer(): void {
        global $CFG;
        $this->resetAfterTest();
        $filepath = $CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_importer.csv';
        $programimporter = new programme_importer(['datafieldid' => $this->cfdid]);
        $programimporter->import($filepath, "comma");
        $records = sprogramme::get_records(['datafieldid' => $this->cfdid]);
        $this->assertCount(2, $records);
        $firstrecord = reset($records);
        $this->assertEquals('CM - phénomènes de transports', $firstrecord->get('intitule_seance'));
        $this->assertEquals('poly et PPT', $firstrecord->get('supports'));
        $this->assertEquals(4.5, $firstrecord->get('cm'));
        $this->assertEquals(5.1, $firstrecord->get('perso_ap'));
        $disciplines = sprogramme_disc::get_all_records_for_programme($firstrecord->get('id'));
        $this->assertCount(2, $disciplines);
        $expecteddisciplines = [
            '1. Animal biology, zoology and cell biology' => 50,
            '1. Medical physics' => 50,
        ];
        foreach ($disciplines as $discipline) {
            $this->assertArrayHasKey($discipline->get_name(), $expecteddisciplines);
            $this->assertEquals($expecteddisciplines[$discipline->get_name()], $discipline->get('percentage'));
        }
        $competencies = sprogramme_comp::get_all_records_for_programme($firstrecord->get('id'));
        $this->assertCount(2, $competencies);
        $expectedcompetencies = [
           "COPREV1 - Évaluer l'état général, le bien-être et l'état nutritionnel d'un animal ou d'un groupe d'animaux" => 10,
           "ST3 - Pratiquer en toute sécurité une sédation, une anesthésie générale et une anesthésie loco-régionale" => 90,
        ];
        foreach ($competencies as $competency) {
            $this->assertArrayHasKey($competency->get_name(), $expectedcompetencies);
            $this->assertEquals($expectedcompetencies[$competency->get_name()], $competency->get('percentage'));
        }
    }


    /**
     * Test the importer with a sample CSV file with a different encoding.
     *
     * @covers \customfield_sprogramme\local\importer\programme_importer::import
     */
    function test_importer_with_encoding(): void {
        global $CFG;
        $this->resetAfterTest();
        $filepath = $CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_importer_encoded_windows.csv';
        $programimporter = new programme_importer(['datafieldid' => $this->cfdid]);
        $programimporter->import($filepath, "comma", "windows-1252");
        $records = sprogramme::get_records(['datafieldid' => $this->cfdid]);
        $this->assertCount(21, $records);
        $firstrecord = reset($records);
        $this->assertEquals('Histologie générale : les tissus', $firstrecord->get('intitule_seance'));
    }
}
