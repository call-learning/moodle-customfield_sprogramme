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

$string['accept'] = 'Accepter';
$string['addmodule'] = 'Ajouter un module';
$string['addrow'] = 'Ajouter une ligne';
$string['approvalemail'] = 'Adresse e-mail d’approbation';
$string['approvalemail_desc'] = 'Adresse e-mail à laquelle envoyer les demandes d’approbation. Utilisez une liste séparée par des virgules.';
$string['cancelrfc'] = 'Annuler la demande de modification';
$string['cachedef_programmedata'] = 'Cache des données du programme';
$string['competencies'] = 'Compétences';
$string['cachedef_columntotals'] = 'Totaux des colonnes';
$string['disciplines'] = 'Disciplines';
$string['edit'] = 'Modifier';
$string['editprogramme'] = 'Modifier le programme';
$string['entity:programme'] = 'Programme';
$string['invaliddata'] = 'Données non valides : {$a}';
$string['maxdisciplines'] = 'Vous ne pouvez pas en ajouter davantage, maximum autorisé : {$a}';
$string['maxpercentage'] = 'Maximum autorisé : {$a} La somme des pourcentages doit être égale à 100';
$string['pluginname'] = 'Champ personnalisé Programme';
$string['programme:courseid'] = 'Identifiant du cours';
$string['programme:intitule_seance'] = 'Intitulé de la séance';
$string['reject'] = 'Rejeter';
$string['removerfc'] = 'Réinitialiser toutes les modifications';
$string['report:programme'] = 'Programme';
$string['resetrfc'] = 'Masquer les modifications proposées';
$string['rfcs'] = 'Demandes {$a}';
$string['row'] = 'Ligne {$a}';
$string['value'] = 'Valeur';
$string['saving'] = 'Enregistrement...';
$string['sprogramme:edit'] = 'Modifier le champ personnalisé Programme';
$string['sprogramme:view'] = 'Afficher le champ personnalisé Programme';
$string['submitdate'] = 'Date de soumission : ';
$string['submitrfc'] = 'Soumettre une demande de modification';
$string['email:rfc:subject'] = '[Programme] Vous avez une demande de modification pour {$a->coursename}';
$string['email:rfc'] = <<<'EOF'

<p>Bonjour,</p>

<p>Une demande de modification a été soumise pour le programme du cours {$a->coursename}.</p>

<p>Veuillez consulter le lien suivant pour examiner la demande :</p>
<p><a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Cordialement,</p>
EOF;
$string['notification:rfc'] = 'Demande de modification';
$string['notification:notifications'] = 'Notifications par e-mail';
$string['rfc:requested'] = 'Demandée';
$string['rfc:submitted'] = 'Soumise';
$string['rfc:accepted'] = 'Acceptée';
$string['rfc:rejected'] = 'Rejetée';
$string['invalidpagetype'] = 'Type de page non valide';
$string['usernotfound'] = 'Utilisateur non trouvé';
$string['rfc:changerequestby'] = 'Demande de modification par {$a}';
$string['rfc:rfcblocked'] = 'Demande bloquée';
$string['rfc:rfcblocked:helptext'] = <<<'EOF'
Une demande de modification a déjà été soumise pour ce programme. Une seule demande peut être active à la fois.
Si vous souhaitez proposer une autre modification, veuillez d’abord en discuter avec la personne concernée.
EOF;
$string['rfc:user'] = 'Utilisateur';
$string['rfc:course'] = 'Cours';
$string['rfc:timecreated'] = 'Date de création';
$string['rfc:status'] = 'Statut';
$string['rfc:actions'] = 'Actions';
$string['rfc:selectcourse'] = 'Sélectionner un cours';
$string['rfc:selectstatus'] = 'Sélectionner un statut';
$string['rfc:view'] = 'Voir';
$string['rfc:help'] = 'Aide';
$string['rfc:helptext'] = <<<'EOF'
<h3>Procédure de soumission d’une demande</h3>
<p>Pour soumettre une demande de modification, commencez par modifier les champs grisés. Une fois terminé, cliquez sur le bouton "Soumettre une demande de modification".</p>
<p>Pour annuler une demande, cliquez sur "Annuler la demande de modification" dans le menu déroulant.</p>
<p>Une fois la demande soumise, le responsable du site recevra un e-mail avec un lien vers la demande.</p>
<p>Une fois la demande acceptée, les modifications seront appliquées au cours.</p>
EOF;

$string['history'] = 'Historique';
$string['cm_help'] = 'Nombre d’heures de cours magistral dispensé par un enseignant à un grand groupe d’étudiants.';
$string['td_help'] = 'Nombre d’heures de travaux dirigés réalisés en petits groupes avec un encadrement pédagogique.';
$string['tp_help'] = 'Nombre d’heures de travaux pratiques consacrés à des expériences ou à l’apprentissage technique.';
$string['tpa_help'] = 'Nombre d’heures de travaux pratiques accompagnés avec un encadrement léger, réalisés de manière plus autonome.';
$string['tc_help'] = 'Nombre d’heures de travaux collectifs effectués en groupe par les étudiants.';
$string['aas_help'] = 'Nombre d’heures d’activités pédagogiques spécifiques, telles que des conférences, des sorties ou des ateliers.';
$string['fmp_help'] = 'Nombre d’heures de formation en milieu professionnel (stages, immersions, etc.).';
$string['perso_av_help'] = 'Estimation du temps de travail personnel attendu avant la séance (préparation, lecture, etc.).';
$string['perso_ap_help'] = 'Estimation du temps de travail personnel après la séance (exercices, révisions, etc.).';
$string['notifications'] = 'Notifications';
$string['sprogramme:editall'] = 'Modifier tous les champs personnalisés Programme';

$string['overaltotals'] = 'Totaux généraux';
$string['overaltotals_help'] = 'Total de toutes les colonnes du tableau. Il s’agit de la somme de toutes les colonnes pour chaque ligne.';