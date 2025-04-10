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
$string['augment'] = 'Show suggested changes';
$string['cancelrfc'] = 'Cancel change request';
$string['changesubmitted'] = 'Change request submitted for {$a}';
$string['competencies'] = 'Competencies';
$string['disciplines'] = 'Disciplines';
$string['edit'] = 'Edit';
$string['editprogramme'] = 'Edit Programme';
$string['entity:programme'] = 'Programme';
$string['invaliddata'] = 'Invalid data: {$a}';
$string['invalidinput'] = 'Invalid input';
$string['maxdisciplines'] = 'You can not any more, max allowed {$a}';
$string['maxpercentage'] = 'Max allowed {$a} The sum of the percentages must be 100';
$string['pluginname'] = 'Programme customfield';
$string['programme:courseid'] = 'Course id';
$string['programme:intitule_seance'] = 'Intitule seance';
$string['reject'] = 'Reject';
$string['removerfc'] = 'Reset all changes';
$string['report:programme'] = 'Programme';
$string['resetrfc'] = 'Reset Table';
$string['rfclocked'] = 'RFC locked';
$string['row'] = 'Row {$a}';
$string['save'] = 'Save';
$string['saving'] = 'Saving...';
$string['sprogramme:edit'] = 'Edit the Programme customfield';
$strign['sprogramme:editall'] = 'Edit all the Programme customfield';
$string['sprogramme:view'] = 'View the Programme customfield';
$string['submitdate'] = 'Submit date: ';
$string['submitrfc'] = 'Submit change request';
$string['email:rfc:subject'] = '[Programme] You have a change request for {$a->coursename}';
$string['email:rfc'] = <<<'EOF'

<p>Hello,</p>

<p>A change request has been submitted for the programme for {$a->coursename}.</p>

<p>Please visit the following link to review the request:</p>
<p><a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Best regards,</p>
EOF;
$string['notification:rfc'] = 'Request for change';
$string['rfc:requested'] = 'Requested';
$string['rfc:submitted'] = 'Submitted';
$string['rfc:accepted'] = 'Accepted';
$string['rfc:rejected'] = 'Rejected';

