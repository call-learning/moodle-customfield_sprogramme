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
 * Plugin strings are defined here.
 *
 * @package     customfield_sprogramme
 * @category    string
 * @copyright   2024 CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accept'] = 'Accept';
$string['addmodule'] = 'Add module';
$string['addrow'] = 'Add row';
$string['approvalemail'] = 'Approval email';
$string['approvalemail_desc'] = 'Email address to send approval requests to. This is a comma separated list of email addresses.';
$string['cancelrfc'] = 'Cancel change request';
$string['cachedef_programmedata'] = 'Programme data cache';
$string['competencies'] = 'Competencies';
$string['cachedef_columntotals'] = 'Column totals';
$string['disciplines'] = 'Disciplines';
$string['edit'] = 'Edit';
$string['editprogramme'] = 'Edit Programme';
$string['entity:programme'] = 'Programme';
$string['invaliddata'] = 'Invalid data: {$a}';
$string['maxdisciplines'] = 'You can not any more, max allowed {$a}';
$string['maxpercentage'] = 'Max allowed {$a} The sum of the percentages must be 100';
$string['pluginname'] = 'Programme customfield';
$string['programme:courseid'] = 'Course id';
$string['programme:intitule_seance'] = 'Intitule seance';
$string['reject'] = 'Reject';
$string['removerfc'] = 'Reset all changes';
$string['report:programme'] = 'Programme';
$string['resetrfc'] = 'Hide suggested changes';
$string['rfcs'] = 'Requests {$a}';
$string['row'] = 'Row {$a}';
$string['value'] = 'Value';
$string['saving'] = 'Saving...';
$string['sprogramme:edit'] = 'Edit the Programme customfield';
$strign['sprogramme:editall'] = 'Edit all the Programme customfield';
$string['sprogramme:view'] = 'View the Programme customfield';
$string['submitdate'] = 'Submit date: ';
$string['submitrfc'] = 'Submit change request';
$string['email:rfc:subject'] = '[Syllabus] Request for programme change for: {$a->coursename}';
$string['email:rfc'] = <<<'EOF'

<p>Hello,</p>
<p>A change request has been submitted for the programme of the following course: {$a->coursename}.</p>
<p>The head of the relevant department is invited to review these changes and confirm their agreement by replying to this message, copying the director of training and the quality manager.</p>
<p>Once this agreement has been communicated, the director of training will proceed with the final validation and update the overall educational framework.</p>
<p>To view the proposed changes, please follow this link and click on the "Edit programme" button: 
<a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Best regards</p>
EOF;
$string['notification:rfc'] = 'Request for change';
$string['notification:notifications'] = 'Email notifications';
$string['rfc:requested'] = 'Requested';
$string['rfc:submitted'] = 'Submitted';
$string['rfc:accepted'] = 'Accepted';
$string['rfc:rejected'] = 'Rejected';
$string['invalidpagetype'] = 'Invalid page type';
$string['usernotfound'] = 'User not found';
$string['rfc:changerequestby'] = 'Change request by {$a}';
$string['rfc:rfcblocked'] = 'RFC blocked';
$string['rfc:rfcblocked:helptext'] = <<<'EOF'
A change request has been submitted for the programme. Only one change request can be submitted at a time.
If you want to make another change, please discuss it with the requestor first.'
EOF;
$string['rfc:user'] = 'User';
$string['rfc:course'] = 'Course';
$string['rfc:timecreated'] = 'Time created';
$string['rfc:status'] = 'Status';
$string['rfc:actions'] = 'Actions';
$string['rfc:selectcourse'] = 'Select course';
$string['rfc:selectstatus'] = 'Select status';
$string['rfc:view'] = 'View';
$string['rfc:help'] = 'Help';
$string['rfc:helptext'] = <<<'EOF'
<h3>The submit request process</h3>
<p>To submit a change request, start changing the grayed fields. When you are done, click on the "Submit change request" button.</p>
<p>To cancel a change request, click on the "Cancel change request" button in the dropdown menu.</p>
<p>Once the change request is submitted, the site manager will receive an email with a link to the change request.</p>
<p>Once the change request is accepted, the changes will be applied to the course.</p>
EOF;

$string['history'] = 'History';
$string['cm_help'] = 'Number of lecture hours given by a teacher to a large group of students.';
$string['td_help'] = 'Number of tutorial hours conducted in small groups with educational support.';
$string['tp_help'] = 'Number of practical work hours dedicated to experiments or technical learning.';
$string['tpa_help'] = 'Number of guided practical work hours with light supervision, carried out more independently.';
$string['tc_help'] = 'Number of group work hours carried out collectively by students.';
$string['aas_help'] = 'Number of hours for specific educational activities, such as lectures, field trips or workshops.';
$string['fmp_help'] = 'Number of hours of work experience in a professional environment (internships, placements, etc.).';
$string['perso_av_help'] = 'Estimated amount of personal preparation time expected before the session (reading, preparation, etc.).';
$string['perso_ap_help'] = 'Estimated amount of personal study time after the session (exercises, revision, etc.).';
$string['notifications'] = 'Notifications';

$string['overaltotals'] = 'Overall totals';
$string['overaltotals_help'] = 'Total of all columns in the table. This is the sum of all the columns for each row.';