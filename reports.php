<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Display information about all the customfield_sprogramme field
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_competvet\local\persistent\situation;
use mod_competvet\reportbuilder\local\systemreports\situations;
require('../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

$context = context_system::instance();
require_login(null, false);

$userid = optional_param('userid', $USER->id, PARAM_INT);
$reportname = optional_param('reportname', 'competencies', PARAM_ALPHANUM);
$currenturl = new moodle_url('/customfield/field/sprogramme/reports.php', [ 'userid' => $userid, 'reportname' => $reportname]);
$PAGE->set_url($currenturl);
$PAGE->set_context($context);
$pagetitle = get_string('report:' . $reportname, 'customfield_sprogramme');
$PAGE->set_title($pagetitle);
$PAGE->set_pagelayout('report');
$returnto = optional_param('returnurl', null, PARAM_URL);

if ($returnto) {
    $PAGE->set_button($OUTPUT->single_button(new moodle_url($returnto), get_string('back')));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
$report = null;
switch ($reportname) {
    case 'competencies':
        $report = \core_reportbuilder\system_report_factory::create(
            \customfield_sprogramme\reportbuilder\local\systemreports\competencies::class,
            $context,
        );
        break;
    case 'disciplines':
        $report = \core_reportbuilder\system_report_factory::create(
            \customfield_sprogramme\reportbuilder\local\systemreports\disciplines::class,
            $context,
        );
    default:
        break;
}
if ($report === null) {
    throw new \moodle_exception('invalidreportid', 'customfield_sprogramme', $currenturl);
}
$report->require_can_view();
if (!empty($report)) {
    echo $report->output();
}
echo $OUTPUT->footer();
