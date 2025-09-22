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

/**
 * Test helper trait
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace customfield_sprogramme\test;

/**
 * Test helper trait
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait testcase_helper_trait {
    /**
     * Assert that the programme data matches the expected data.
     *
     * @param array $expected The expected programme data.
     * @param array $actual The actual programme data.
     */
    public function assert_programme_data_equals(array $expected, array $actual): void {
        // Remove any fields that are not in the expected data.
        foreach (array_values($actual) as $index => $module) {
            $this->assertEquals($expected[$index]['modulesortorder'], $module['modulesortorder']);
            $this->assertEquals($expected[$index]['modulename'], $module['modulename']);
            foreach ($module['rows'] as $rowindex => $row) {
                $expectedrow = $expected[$index]['rows'][$rowindex];
                $this->assertEquals(
                    $expectedrow['sortorder'],
                    $row['sortorder'],
                    "Row sortorder does not match for module({$module['modulename']}) row({$rowindex})"
                );
                $this->assertCount(
                    count($expectedrow['disciplines']),
                    $row['disciplines'],
                    "Discipline count does not match for module({$module['modulename']}) row({$rowindex})"
                );
                foreach ($row['disciplines'] as $discindex => $disc) {
                    $this->assertEquals(
                        $expectedrow['disciplines'][$discindex]['id'],
                        $disc['id'],
                        "Discipline id does not match for module({$module['modulename']}) row({$rowindex})"
                        . " discipline {$disc['id']})"
                    );
                    $this->assertEquals(
                        $expectedrow['disciplines'][$discindex]['percentage'],
                        $disc['percentage'],
                        "Discipline percentage does not match for module({$module['modulename']}) row({$rowindex})"
                        . " discipline {$disc['id']})"
                    );
                }
                $this->assertCount(
                    count($expectedrow['competencies']),
                    $row['competencies'],
                    "Competencies count does not match for module({$module['modulename']}) row({$rowindex})"
                );
                foreach ($row['competencies'] as $compindex => $comp) {
                    $this->assertEquals(
                        $expectedrow['competencies'][$compindex]['id'],
                        $comp['id'],
                        "Competency id does not match for module({$module['modulename']}) row({$rowindex})"
                        . " competency {$comp['id']})"
                    );
                    $this->assertEquals(
                        $expectedrow['competencies'][$compindex]['percentage'],
                        $comp['percentage'],
                        "Competency percentage does not match for module({$module['modulename']}) row({$rowindex})"
                        . " competency {$comp['id']})"
                    );
                }
                foreach ($row['cells'] as $cellindex => $cell) {
                    foreach (['column', 'value', 'type'] as $field) {
                        $this->assertEquals(
                            $expectedrow['cells'][$cellindex][$field],
                            $cell[$field],
                            "Cell ({$cellindex}:{$field}) does not match for module {$module['modulename']} row {$rowindex}"
                            . " cell ({$cellindex})  column ($cell[$field])"
                        );
                    }
                }
            }
        }
    }
}
