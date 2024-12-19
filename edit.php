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
 * Edit customfield sprogramme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);

$url = new moodle_url('/customfield/field/sprogramme/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_course::instance($courseid));

$PAGE->set_heading($SITE->fullname);
$PAGE->set_secondary_navigation(false);

$programm = new customfield_sprogramme\output\programme();
$output = $PAGE->get_renderer('customfield_sprogramme');
echo $OUTPUT->header();
echo $output->render($programm);
echo $OUTPUT->footer();
