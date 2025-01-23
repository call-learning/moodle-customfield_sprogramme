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
 * TODO describe file test
 *
 * @package    customfield_sprogramme
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
use customfield_sprogramme\output\formfield;
use customfield_sprogramme\output\programme;

require_login();

$courseid = optional_param('courseid', 0, PARAM_INT);

$url = new moodle_url('/customfield/field/sprogramme/test.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context(context_course::instance($courseid));

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('customfield_sprogramme');
$formfield = new formfield();
echo $renderer->render($formfield);

$programm = new programme($courseid);
echo $renderer->render($programm);

echo $OUTPUT->footer();
