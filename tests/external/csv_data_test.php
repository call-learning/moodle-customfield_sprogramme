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

namespace external;

use core_external\external_api;
use customfield_sprogramme\external\accept_rfc;
use customfield_sprogramme\external\csv_data;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the csv_data class.
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \customfield_sprogramme\external\csv_data
 */
final class csv_data_test extends \externallib_advanced_testcase {
    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function csv_data(...$params) {
        $acceptrfc = csv_data::execute(...$params);
        return external_api::clean_returnvalue(csv_data::execute_returns(), $acceptrfc);
    }
}
