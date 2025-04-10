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
 * Strings for component 'customfield_sprogramme', language 'fr'
 *
 * @package    customfield_sprogramme
 * @category   string
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmodule'] = 'Ajouter un module';
$string['addrow'] = 'Ajouter une ligne';
$string['customfield/sprogramme:edit'] = 'Modifier le champ Programme';
$string['customfield/sprogramme:view'] = 'Voir le champ Programme';
$string['disciplines'] = 'Disciplines';
$string['edit'] = 'Modifier';
$string['editprogramme'] = 'Modifier Programme';
$string['invaliddata'] = 'Données invalides : {$a}';
$string['pluginname'] = 'Champ Programme';
$string['row'] = 'Ligne {$a}';
$string['save'] = 'Enregistrer';
$string['saving'] = 'Enregistrement...';
$string['email:rfc:subject'] = '[Programme] Vous avez une demande de changement pour {$a->coursename}';

$string['email:rfc'] = <<<'EOF'
<p>Bonjour,</p>
<p>Une demande de changement a été soumise pour le programme de {$a->coursename}.</p>
<p>Veuillez visiter le lien suivant pour examiner la demande :</p>
<p><a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Cordialement,</p>
EOF;
