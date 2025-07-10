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
$string['email:rfc:subject'] = '[Syllabus] Demande de modification de programme pour l\'UC :{$a->coursename}';
$string['email:rfc'] = <<<'EOF'

<p>Bonjour,</p>

<p>Une demande de modification de programme a été soumise pour l'UC suivante :{$a->coursename}.</p>

<p>Le chef de département concerné est invité à examiner ces modifications et à confirmer son accord en répondant à ce message, en copie au directeur des formations et au responsable qualité.</p>
<p>Une fois cet accord transmis, la direction des formations procédera à la validation finale, puis à la mise à jour de la maquette pédagogique globale.</p>
<p>Pour consulter les modifications proposées, veuillez suivre ce lien et cliquer sur le bouton "modifier le programme" :
<a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Bien cordialement</p>
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

$string['dd_rse_help'] = 'Développement Durable / Responsabilité Sociétale et Environnementale : Cette case indique si la séance traite intégralement ou en partie de notions en lien avec le domaine DD / RSE.';
$string['intitule_seance_help'] = 'Le nom de l’exercice d’enseignement est renseigné dans cette case. Il doit correspondre exactement au mémo indiqué dans Hyperplanning. Cet intitulé commence par le type d’exercice (CM, TD, TP, …) et son ordre dans le ruban pédagogique. Par exemple : « CM03 - Carrière de la vache laitière 1/2 ».';
$string['cm_help'] = 'Cours Magistral : Enseignement théorique dispensé à un groupe entier ou partiel d\'étudiants. L\'enseignement peut être avec ou sans l\'aide de matériel pédagogique, d\'animaux de démonstration ou de spécimens. La caractéristique essentielle est qu\'il n\'y a pas d\'implication pratique des étudiants dans le matériel discuté. Ils écoutent et ne manipulent pas physiquement.';
$string['td_help'] = 'Travaux Dirigés : Séances d’enseignement dirigé au cours desquelles les étudiants travaillent seuls ou en équipe sur des aspects théoriques, préparés à partir de documents, d’articles, etc. Les étudiants réfléchissent et interagissent sur des concepts. La séance est animée par des exercices, des discussions et, si possible, des études de cas (apprentissage par résolution de problèmes par exemple).';
$string['tp_help'] = 'Travaux Pratiques non cliniques : Séances d’enseignement où les étudiants manipulent eux-mêmes les ressources pédagogiques (logiciels, microscopes, expé en labo, etc) sans manipulation d’animaux, d’organes ou de mannequins.';
$string['tpa_help'] = 'TP sur animaux sains : Séances d’enseignement où les étudiants travaillent eux-mêmes sur des animaux sains, des pièces anatomiques, des mannequins, des carcasses, etc. (par exemple : inspection ante mortem et post mortem, hygiène alimentaire, etc.). Toutes les activités VetSims sont incluses dans cette catégorie.';
$string['tc_help'] = 'Travaux Cliniques : Séances d\'enseignement pratique effectuées par les étudiants dans un environnement clinique (médecine individuelle ou collective) incluant les rotations cliniques intra et extra-muros (dont ambulante) sous la supervision d’un enseignant, et l’autopsie.';
$string['aas_help'] = 'Auto-Apprentissage Supervisé : Enseignement comprenant des séquences d’apprentissage individuel en autonomie où les élèves utilisent un matériel pédagogique disponible (et peuvent obtenir, à leur demande, une aide ponctuelle des enseignants) et s\'auto-évaluent (e-learning par exemple).';
$string['fmp_help'] = 'Formation en Milieu Professionnel dans le cadre d’une UC : Périodes de formation qui font partie intégrante du programme d’études, mais qui sont suivies en dehors de l’établissement et sous la supervision d’un enseignant non académique (par exemple un praticien).';
$string['perso_av_help'] = 'Temps de travail personnel estimé nécessaire pour préparer en amont la séance / l’exercice. Ce temps de travail inclut entre autres le temps passé à réaliser des auto-évaluations de pré-requis avant la séance.';
$string['perso_ap_help'] = 'Temps de travail personnel estimé nécessaire pour assimiler la séance / l’exercice. Ce temps de travail inclut le temps passé à réviser pour l’évaluation intermédiaire et/ou l’examen final.';
$string['consignes_help'] = 'Cette case renseigne sur tout ce que doit faire l’étudiant pour préparer la séance / l’exercice avant de s’y présenter. Ce qui n’est pas indiqué ici ne peut pas être exigé lors de la séance / l’exercice.';
$string['supports_help'] = 'Cette case renseigne sur les supports pédagogiques indispensables à la préparation de la séance / l’exercice et à sa révision. Seul le matériel pédagogique listé dans cette case est considéré comme indispensable. S’il ne l’est pas, il n’est que facultatif et complémentaire.';
$string['disciplines_help'] = 'Cette case indique les disciplines AEEEV (de 1 à 3 maximum) qui sont concernées par la séance / l’exercice, et leurs % respectifs au sein de la séance (par exemple, 10% pour « 2. Immunology », 60% pour « 2. Parasitology », et 30% pour « 4.FPA Preventive medicine ». La somme doit faire 100%.';
$string['competencies_help'] = 'Cette case indique les compétences (de 1 à 3 maximum) du référentiel national qui sont concernées par la séance / l’exercice, et leurs % respectifs au sein de la séance. La somme doit faire 100%.';

$string['notifications'] = 'Notifications';
$string['sprogramme:editall'] = 'Modifier tous les champs personnalisés Programme';

$string['overaltotals'] = 'Totaux';
$string['overaltotals_help'] = 'Total de toutes les colonnes du tableau. Il s’agit de la somme de toutes les colonnes pour chaque ligne.';