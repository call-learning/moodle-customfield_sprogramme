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

$string['aas_help'] = 'Supervised Self-Learning: Teaching including sequences of individual autonomous learning where students use available teaching materials (and can obtain, upon request, occasional help from teachers) and self-evaluate (e-learning for example).';
$string['accept'] = 'Accept';
$string['addmodule'] = 'Add module';
$string['addrow'] = 'Add row';
$string['alreadyset'] = 'Already set for this row.';
$string['approvalemail'] = 'Approval email';
$string['approvalemail_desc'] = 'Email address to send approval requests to. This is a comma separated list of email addresses.';
$string['cachedef_columntotals'] = 'Column totals';
$string['cachedef_programmedata'] = 'Programme data cache';
$string['cancel'] = 'Cancel';
$string['cancelrfc'] = 'Cancel change request';
$string['closewithoutsaving'] = 'Close without saving';
$string['cm_help'] = 'Main Lecture: Theoretical teaching given to a whole or partial group of students. Teaching can be with or without the aid of teaching materials, demonstration animals, or specimens. The essential characteristic is that there is no practical involvement of students in the material discussed. They listen and do not physically manipulate.';
$string['competencies'] = 'Competencies';
$string['competencies_help'] = 'This field indicates the competencies (1 to 3 maximum) from the national framework that are concerned by the session/exercise, and their respective percentages within the session. The sum must equal 100%.';
$string['competency:name'] = 'Name';
$string['competency:parent'] = 'Parent';
$string['competency:sortorder'] = 'Sort order';
$string['competency:type'] = 'Type';
$string['competency:uniqueid'] = 'Unique ID';
$string['competency_assignment:percentage'] = 'Percentage';
$string['competency_assignment:percentagewithlabel'] = 'Percentage with label';
$string['confirm'] = 'Confirm';
$string['consignes_help'] = 'This field indicates everything the student must do to prepare for the session/exercise before attending. Anything not indicated here cannot be required during the session/exercise.';
$string['csvdelimiter'] = 'CSV delimiter';
$string['csvfile'] = 'CSV file';
$string['dd_rse_help'] = 'Sustainable Development / Social and Environmental Responsibility: This checkbox indicates whether the session fully or partially addresses concepts related to the SD / SER domain.';
$string['discipline:name'] = 'Name';
$string['discipline:parent'] = 'Parent';
$string['discipline:sortorder'] = 'Sort order';
$string['discipline:type'] = 'Type';
$string['discipline:uniqueid'] = 'Unique ID';
$string['discipline_assignment:percentage'] = 'Percentage';
$string['discipline_assignment:percentagewithlabel'] = 'Percentage with label';
$string['disciplines'] = 'Disciplines';
$string['disciplines_help'] = 'This field indicates the AEEEV disciplines (1 to 3 maximum) that are concerned by the session/exercise, and their respective percentages within the session (for example, 10% for "2. Immunology", 60% for "2. Parasitology", and 30% for "4.FPA Preventive medicine". The sum must equal 100%.';
$string['edit'] = 'Edit';
$string['editprogramme'] = 'Edit Programme';
$string['email:rfc'] = <<<'EOF'

<p>Hello,</p>
<p>A change request has been submitted for the programme of the following course: {$a->coursename}.</p>
<p>The head of the relevant department is invited to review these changes and confirm their agreement by replying to this message, copying the director of training and the quality manager.</p>
<p>Once this agreement has been communicated, the director of training will proceed with the final validation and update the overall educational framework.</p>
<p>To view the proposed changes, please follow this link and click on the "Edit programme" button:
<a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Best regards</p>
EOF;
$string['email:rfc:subject'] = '[Syllabus] Request for programme change for: {$a->coursename}';
$string['encoding'] = 'Encoding';
$string['entity:competency'] = 'Competency';
$string['entity:competency_assignment'] = 'Competency assignment';
$string['entity:discipline'] = 'Discipline';
$string['entity:discipline_assignment'] = 'Discipline assignment';
$string['entity:module'] = 'Module';
$string['entity:programme'] = 'Programme';
$string['fmp_help'] = 'Professional Training in the context of a UC: Training periods that are an integral part of the study program, but are followed outside the institution and under the supervision of a non-academic teacher (for example, a practitioner).';
$string['history'] = 'History';
$string['intitule_seance_help'] = 'The name of the teaching exercise is entered in this field. It must match exactly the memo indicated in Hyperplanning. This title starts with the type of exercise (CM, TD, TP, etc.) and its order in the educational ribbon. For example: "CM03 - Dairy Cow Career 1/2".';
$string['invaliddata'] = 'Invalid data: {$a}';
$string['invalidpagetype'] = 'Invalid page type';
$string['invalidvalue'] = 'Invalid value for column {$a->column}: {$a->value}';
$string['maxdisciplines'] = 'You can not any more, max allowed reached';
$string['maxpercentage'] = 'Max allowed {$a} The sum of the percentages must be 100';
$string['module:name'] = 'Module Name';
$string['module:sortorder'] = 'Module';
$string['mutatecourseidtofieldid'] = 'Mutate courseid to fieldid';
$string['notification:notifications'] = 'Email notifications';
$string['notification:rfc'] = 'Request for change';
$string['notifications'] = 'Notifications';
$string['overaltotals'] = 'Overall totals';
$string['overaltotals_help'] = 'Total of all columns in the table. This is the sum of all the columns for each row.';
$string['perso_ap_help'] = 'Estimated personal work time needed to assimilate the session/exercise. This work time includes the time spent revising for the mid-term assessment and/or final exam.';
$string['perso_av_help'] = 'Estimated personal work time needed to prepare in advance for the session/exercise. This work time includes, among other things, the time spent completing prerequisite self-assessments before the session.';
$string['pluginname'] = 'Programme customfield';
$string['programme:aas'] = 'AAS';
$string['programme:cct_ept'] = 'CCT EPT';
$string['programme:cm'] = 'CM';
$string['programme:consignes'] = 'Instructions to prepare for the session';
$string['programme:datafieldid'] = 'Data field id';
$string['programme:dd_rse'] = 'DD / RSE';
$string['programme:enabled'] = '{$a} enabled';
$string['programme:enabledbydefault'] = 'Programme Enabled By default';
$string['programme:fmp'] = 'FMP';
$string['programme:intitule_seance'] = 'Session title or exercise';
$string['programme:perso_ap'] = 'Perso ap';
$string['programme:perso_av'] = 'Perso av';
$string['programme:sequence'] = 'Sequence';
$string['programme:sortorder'] = 'Sort order';
$string['programme:supports'] = 'Essential teaching materials';
$string['programme:tc'] = 'TC';
$string['programme:td'] = 'TD';
$string['programme:timecreated'] = 'Time created';
$string['programme:timemodified'] = 'Time modified';
$string['programme:tp'] = 'TP';
$string['programme:tpa'] = 'TPA';
$string['programme:type_ae'] = 'Type AE';
$string['programme:uc'] = 'UC';
$string['programme:usermodified'] = 'Modified by';
$string['reject'] = 'Reject';
$string['removerfc'] = 'Reset all changes';
$string['report:competencies'] = 'Competencies Report';
$string['report:disciplines'] = 'Disciplines Report';
$string['report:programme'] = 'Programme';
$string['report:rfcs'] = 'Change requests';
$string['report:rfctotals'] = 'Cahange requests totals';
$string['resetrfc'] = 'Hide suggested changes';
$string['rfc:accepted'] = 'Accepted';
$string['rfc:actions'] = 'Actions';
$string['rfc:changerequestby'] = 'Change request by {$a}';
$string['rfc:course'] = 'Course';
$string['rfc:help'] = 'Help';
$string['rfc:helptext'] = <<<'EOF'
<strong><br>Submit a change request</strong><br>
The editable fields in the table can be freely modified (titles, instructions, supports, etc.) to allow for regular updates.
The grayed-out fields (hourly volumes) can only be modified after validation by the DEVE, in conjunction with the department heads, in order to maintain good traceability of updates to the hourly volumes.
To propose a modification, enter the new value (in red), then click on "Submit a change request".
The DEVE will receive a notification and will contact you if necessary.
The schedule changes will only be visible to students after validation.
EOF;
$string['rfc:rejected'] = 'Rejected';
$string['rfc:requested'] = 'Requested';
$string['rfc:rfcblocked'] = 'RFC blocked';
$string['rfc:rfcblocked:helptext'] = <<<'EOF'
A change request has been submitted for the programme. Only one change request can be submitted at a time.
If you want to make another change, please discuss it with the requestor first.'
EOF;
$string['rfc:selectcourse'] = 'Select course';
$string['rfc:selectstatus'] = 'Select status';
$string['rfc:status'] = 'Status';
$string['rfc:submitted'] = 'Submitted';
$string['rfc:timecreated'] = 'Time created';
$string['rfc:user'] = 'User';
$string['rfc:view'] = 'View';
$string['rfcs'] = 'Requests {$a}';
$string['row'] = 'Row {$a}';
$string['saving'] = 'Saving...';
$string['sprogramme:edit'] = 'Edit the Programme customfield';
$string['sprogramme:editall'] = 'Edit all the Programme customfield';
$string['sprogramme:view'] = 'View the Programme customfield';
$string['submitdate'] = 'Submit date: ';
$string['submitrfc'] = 'Submit change request';
$string['supports_help'] = 'This field indicates the essential teaching materials needed for preparing for the session/exercise and for revision. Only the teaching materials listed in this field are considered essential. If it is not essential, it is only optional and complementary.';
$string['tc_help'] = 'Clinical Work: Practical teaching sessions performed by students in a clinical environment (individual or collective medicine) including clinical rotations both in-house and off-site (including ambulatory) under the supervision of a teacher, and autopsy.';
$string['td_help'] = 'Directed Work: Teaching sessions where students work alone or in teams on theoretical aspects, prepared from documents, articles, etc. Students reflect and interact on concepts. The session is animated by exercises, discussions, and, if possible, case studies (problem-solving learning for example).';
$string['tp_help'] = 'Practical Work non-clinical: Teaching sessions where students themselves manipulate teaching resources (software, microscopes, lab experiments, etc.) without handling animals, organs, or mannequins.';
$string['tpa_help'] = 'Practical Work on healthy animals: Teaching sessions where students work themselves on healthy animals, anatomical parts, mannequins, carcasses, etc. (for example: ante mortem and post mortem inspection, food hygiene, etc.). All VetSims activities are included in this category.';
$string['unsavedchanges'] = 'You have unsaved changes. Do you want to close the form without saving?';
$string['uploadcsv'] = 'Upload CSV file';
$string['usernotfound'] = 'User not found';
$string['value'] = 'Value';
