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

namespace customfield_sprogramme\external;

use core_external\external_api;
use customfield_sprogramme\external\get_programme_history;
use customfield_sprogramme\local\persistent\sprogramme_rfc;
use customfield_sprogramme\local\programme_manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the get_programme_history class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\get_programme_history
 */
final class get_programme_history_test extends \externallib_advanced_testcase {
    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_programme_history(...$params) {
        $acceptrfc = get_programme_history::execute(...$params);
        return external_api::clean_returnvalue(get_programme_history::execute_returns(), $acceptrfc);
    }
    /**
     * Test the get programme history function.
     */
    public function test_programme_history(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/customfield/field/sprogramme/tests/fixtures/programme_data.php');

        $sampledata = get_sample_programme_data();
        $pgenerator = $this->getDataGenerator()->get_plugin_generator('customfield_sprogramme');
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfcat = $cfgenerator->create_category();
        $cfield = $cfgenerator->create_field(
            ['categoryid' => $cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'sprogramme']
        );
        $course = $this->getDataGenerator()->create_course();
        $cfdata = $cfgenerator->add_instance_data($cfield, $course->id, 1);

        $pm = new programme_manager($cfdata->get('id'));
        $pgenerator->create_programme($cfdata->get('id'), $sampledata[0]);
        $teacher1 = $this->getDataGenerator()->create_user();
        $this->assertFalse($pm->has_history());
        $rfc1 = $pgenerator->create_rfc(
            $cfdata->get('id'),
            usercreated: $teacher1->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($sampledata[0])
        );

        // Create another rfc.
        $modifieddata = $sampledata[0];
        $modifieddata[0]['rows'][0]['dd_rse'] = 'New RSE Value';
        $rfc2 = $pgenerator->create_rfc(
            $cfdata->get('id'),
            usercreated: $teacher1->id,
            type: sprogramme_rfc::RFC_SUBMITTED,
            snapshot: json_encode($modifieddata)
        );
        // Now we should have history.
        $history = $pm->get_history($rfc2->id);
        $this->assertCount(1, $history['rfcs']);
        $this->assertCount(1, $history['modules']);
        // Test with rfcid.
        $history = $pm->get_history($rfc1->id);
        $this->assertCount(1, $history['rfcs']);
        $this->assertCount(1, $history['modules']);
    }
}
