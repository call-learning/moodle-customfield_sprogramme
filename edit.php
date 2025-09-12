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

global $PAGE, $OUTPUT;

use customfield_sprogramme\output\formfield;
use customfield_sprogramme\output\programme;
use customfield_sprogramme\output\viewrfcs;
use customfield_sprogramme\setup;

$datafieldid = optional_param('datafieldid', 0, PARAM_INT);
$pagetype = optional_param('pagetype', 'course', PARAM_ALPHANUMEXT);

if ($datafieldid) {
    $courseid = \customfield_sprogramme\utils::get_instanceid_from_datafieldid($datafieldid);
    $context = context_course::instance($courseid);
    $course = get_course($courseid);
} else {
    $context = context_system::instance();
    $course = get_site();
}
require_login($course);
$url = new moodle_url('/customfield/field/sprogramme/edit.php', ['datafieldid' => $datafieldid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($course->fullname);
$PAGE->set_secondary_navigation(true);

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('customfield_sprogramme');

switch ($pagetype) {
    case 'course':
        $formfield = new formfield($datafieldid);
        echo $renderer->render($formfield);
        $programm = new programme($datafieldid);
        echo $renderer->render($programm);
        break;
    case 'viewrfcs':
        $viewnotification = new viewrfcs($datafieldid);
        echo $renderer->render($viewnotification);
        break;
    case 'setup':
        setup::fill_disclist();
        setup::fill_complist();
        break;
    default:
        echo $OUTPUT->notification(get_string('invalidpagetype', 'customfield_sprogramme'), 'error');
        break;
}

echo $OUTPUT->footer();
