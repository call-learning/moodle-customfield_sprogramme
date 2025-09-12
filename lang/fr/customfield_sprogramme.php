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

$string['aas_help'] = 'Auto-Apprentissage Supervisé : Enseignement comprenant des séquences d’apprentissage individuel en autonomie où les élèves utilisent un matériel pédagogique disponible (et peuvent obtenir, à leur demande, une aide ponctuelle des enseignants) et s\'auto-évaluent (e-learning par exemple).';
$string['accept'] = 'Accepter';
$string['addmodule'] = 'Ajouter un module';
$string['addrow'] = 'Ajouter une ligne';
$string['alreadyset'] = 'Déjà définie pour cette ligne.';
$string['approvalemail'] = 'Adresse e-mail d’approbation';
$string['approvalemail_desc'] = 'Adresse e-mail à laquelle envoyer les demandes d’approbation. Utilisez une liste séparée par des virgules.';
$string['cachedef_columntotals'] = 'Totaux des colonnes';
$string['cachedef_programmedata'] = 'Cache des données du programme';
$string['cancel'] = 'Annuler';
$string['cancelrfc'] = 'Annuler la demande de modification';
$string['closewithoutsaving'] = 'Fermer sans enregistrer';
$string['cm_help'] = 'Cours Magistral : Enseignement théorique dispensé à un groupe entier ou partiel d\'étudiants. L\'enseignement peut être avec ou sans l\'aide de matériel pédagogique, d\'animaux de démonstration ou de spécimens. La caractéristique essentielle est qu\'il n\'y a pas d\'implication pratique des étudiants dans le matériel discuté. Ils écoutent et ne manipulent pas physiquement.';
$string['competencies'] = 'Compétences';
$string['competencies_help'] = 'Cette case indique les compétences (de 1 à 3 maximum) du référentiel national qui sont concernées par la séance / l’exercice, et leurs % respectifs au sein de la séance. La somme doit faire 100%.';
$string['competency:name'] = 'Nom';
$string['competency:parent'] = 'Parent';
$string['competency:sortorder'] = 'Ordre de tri';
$string['competency:type'] = 'Type';
$string['competency:uniqueid'] = 'Identifiant unique';
$string['competency_assignment:percentage'] = 'Pourcentage';
$string['competency_assignment:percentagewithlabel'] = 'Pourcentage avec libellé';
$string['confirm'] = 'Confirmer';
$string['consignes_help'] = 'Cette case renseigne sur tout ce que doit faire l’étudiant pour préparer la séance / l’exercice avant de s’y présenter. Ce qui n’est pas indiqué ici ne peut pas être exigé lors de la séance / l’exercice.';
$string['dd_rse_help'] = 'Développement Durable / Responsabilité Sociétale et Environnementale : Cette case indique si la séance traite intégralement ou en partie de notions en lien avec le domaine DD / RSE.';
$string['discipline:name'] = 'Nom';
$string['discipline:parent'] = 'Parent';
$string['discipline:sortorder'] = 'Ordre de tri';
$string['discipline:type'] = 'Type';
$string['discipline:uniqueid'] = 'Identifiant unique';
$string['discipline_assignment:percentage'] = 'Pourcentage';
$string['discipline_assignment:percentagewithlabel'] = 'Pourcentage avec libellé';
$string['disciplines'] = 'Disciplines';
$string['disciplines_help'] = 'Cette case indique les disciplines AEEEV (de 1 à 3 maximum) qui sont concernées par la séance / l’exercice, et leurs % respectifs au sein de la séance (par exemple, 10% pour « 2. Immunology », 60% pour « 2. Parasitology », et 30% pour « 4.FPA Preventive medicine ». La somme doit faire 100%.';
$string['edit'] = 'Modifier';
$string['editprogramme'] = 'Modifier le programme';
$string['email:rfc'] = <<<'EOF'

<p>Bonjour,</p>

<p>Une demande de modification de programme a été soumise pour l'UC suivante :{$a->coursename}.</p>

<p>Le chef de département concerné est invité à examiner ces modifications et à confirmer son accord en répondant à ce message, en copie au directeur des formations et au responsable qualité.</p>
<p>Une fois cet accord transmis, la direction des formations procédera à la validation finale, puis à la mise à jour de la maquette pédagogique globale.</p>
<p>Pour consulter les modifications proposées, veuillez suivre ce lien et cliquer sur le bouton "modifier le programme" :
<a href="{$a->programmelink}">{$a->programmelink}</a></p>
<p>Bien cordialement</p>
EOF;
$string['email:rfc:subject'] = '[Syllabus] Demande de modification de programme pour l\'UC :{$a->coursename}';
$string['entity:competency'] = 'Compétence';
$string['entity:competency_assignment'] = 'Affectation de compétence';
$string['entity:discipline'] = 'Discipline';
$string['entity:discipline_assignment'] = 'Affectation de discipline';
$string['entity:module'] = 'Module';
$string['entity:programme'] = 'Programme';
$string['fmp_help'] = 'Formation en Milieu Professionnel dans le cadre d’une UC : Périodes de formation qui font partie intégrante du programme d’études, mais qui sont suivies en dehors de l’établissement et sous la supervision d’un enseignant non académique (par exemple un praticien).';
$string['history'] = 'Historique';
$string['intitule_seance_help'] = 'Le nom de l’exercice d’enseignement est renseigné dans cette case. Il doit correspondre exactement au mémo indiqué dans Hyperplanning. Cet intitulé commence par le type d’exercice (CM, TD, TP, …) et son ordre dans le ruban pédagogique. Par exemple : « CM03 - Carrière de la vache laitière 1/2 ».';
$string['invaliddata'] = 'Données non valides : {$a}';
$string['invalidpagetype'] = 'Type de page non valide';
$string['invalidvalue'] = 'Invalid value for column {$a->column}: {$a->value}';
$string['maxdisciplines'] = 'Vous ne pouvez pas en ajouter davantage';
$string['maxpercentage'] = 'Maximum autorisé : {$a} La somme des pourcentages doit être égale à 100';
$string['module:name'] = 'Nom du Module';
$string['module:sortorder'] = 'Module';
$string['mutatecourseidtofieldid'] = 'Changer courseid en fieldid';
$string['notification:notifications'] = 'Notifications par e-mail';
$string['notification:rfc'] = 'Demande de modification';
$string['notifications'] = 'Notifications';
$string['overaltotals'] = 'Totaux';
$string['overaltotals_help'] = 'Total de toutes les colonnes du tableau. Il s’agit de la somme de toutes les colonnes pour chaque ligne.';
$string['perso_ap_help'] = 'Temps de travail personnel estimé nécessaire pour assimiler la séance / l’exercice. Ce temps de travail inclut le temps passé à réviser pour l’évaluation intermédiaire et/ou l’examen final.';
$string['perso_av_help'] = 'Temps de travail personnel estimé nécessaire pour préparer en amont la séance / l’exercice. Ce temps de travail inclut entre autres le temps passé à réaliser des auto-évaluations de pré-requis avant la séance.';
$string['pluginname'] = 'Champ personnalisé Programme';
$string['programme:aas'] = 'AAS';
$string['programme:cct_ept'] = 'CCT EPT';
$string['programme:cm'] = 'CM';
$string['programme:consignes'] = 'Consignes';
$string['programme:datafieldid'] = 'Identifiant du champ de cours';
$string['programme:dd_rse'] = 'DD RSE';
$string['programme:enabled'] = 'Programme activé';
$string['programme:enabledbydefault'] = 'Programme activé par défaut';
$string['programme:fmp'] = 'FMP';
$string['programme:intitule_seance'] = 'Intitulé de la séance';
$string['programme:perso_ap'] = 'Travail personnel après';
$string['programme:perso_av'] = 'Travail personnel avant';
$string['programme:sequence'] = 'Séquence';
$string['programme:sortorder'] = 'Ordre de tri';
$string['programme:supports'] = 'Supports';
$string['programme:tc'] = 'TC';
$string['programme:td'] = 'TD';
$string['programme:timecreated'] = 'Date de création';
$string['programme:timemodified'] = 'Date de modification';
$string['programme:tp'] = 'TP';
$string['programme:tpa'] = 'TPA';
$string['programme:type_ae'] = 'Type AE';
$string['programme:uc'] = 'UC';
$string['programme:usermodified'] = 'Modifié par';
$string['reject'] = 'Rejeter';
$string['removerfc'] = 'Réinitialiser toutes les modifications';
$string['report:competencies'] = 'Rapport des compétences';
$string['report:disciplines'] = 'Rapport des disciplines';
$string['report:programme'] = 'Programme';
$string['resetrfc'] = 'Masquer les modifications proposées';
$string['rfc:accepted'] = 'Acceptée';
$string['rfc:actions'] = 'Actions';
$string['rfc:changerequestby'] = 'Demande de modification par {$a}';
$string['rfc:course'] = 'Cours';
$string['rfc:help'] = 'Aide';
$string['rfc:helptext'] = <<<'EOF'
<strong>Soumettre une demande de modification</strong><br>
Les champs éditables du tableau peuvent être modifiés librement (intitulés, consignes, supports, etc.). pour permettre des mises à jour régulières.
Les champs grisés (volumes horaires) ne sont modifiables qu’après validation par la DEVE, en lien avec les chefs de département, afin de garder une bonne traçabilité sur les mises à jour des volumes horaires.
Pour proposer une modification, saisissez la nouvelle valeur (en rouge), puis cliquez sur "Soumettre une demande de modification".
La DEVE recevra une notification et vous contactera si nécessaire.
Les changements horaires ne seront visibles des étudiants qu’après validation.
EOF;
$string['rfc:rejected'] = 'Rejetée';
$string['rfc:requested'] = 'Demandée';
$string['rfc:rfcblocked'] = 'Demande bloquée';
$string['rfc:rfcblocked:helptext'] = <<<'EOF'
Une demande de modification a déjà été soumise pour ce programme. Une seule demande peut être active à la fois.
Si vous souhaitez proposer une autre modification, veuillez d’abord en discuter avec la personne concernée.
EOF;
$string['rfc:selectcourse'] = 'Sélectionner un cours';
$string['rfc:selectstatus'] = 'Sélectionner un statut';
$string['rfc:status'] = 'Statut';
$string['rfc:submitted'] = 'Soumise';
$string['rfc:timecreated'] = 'Date de création';
$string['rfc:user'] = 'Utilisateur';
$string['rfc:view'] = 'Voir';
$string['rfcs'] = 'Demandes {$a}';
$string['row'] = 'Ligne {$a}';
$string['saving'] = 'Enregistrement...';
$string['sprogramme:edit'] = 'Modifier le champ personnalisé Programme';
$string['sprogramme:editall'] = 'Modifier tous les champs personnalisés Programme';
$string['sprogramme:view'] = 'Afficher le champ personnalisé Programme';
$string['submitdate'] = 'Date de soumission : ';
$string['submitrfc'] = 'Soumettre une demande de modification';
$string['supports_help'] = 'Cette case renseigne sur les supports pédagogiques indispensables à la préparation de la séance / l’exercice et à sa révision. Seul le matériel pédagogique listé dans cette case est considéré comme indispensable. S’il ne l’est pas, il n’est que facultatif et complémentaire.';
$string['tc_help'] = 'Travaux Cliniques : Séances d\'enseignement pratique effectuées par les étudiants dans un environnement clinique (médecine individuelle ou collective) incluant les rotations cliniques intra et extra-muros (dont ambulante) sous la supervision d’un enseignant, et l’autopsie.';
$string['td_help'] = 'Travaux Dirigés : Séances d’enseignement dirigé au cours desquelles les étudiants travaillent seuls ou en équipe sur des aspects théoriques, préparés à partir de documents, d’articles, etc. Les étudiants réfléchissent et interagissent sur des concepts. La séance est animée par des exercices, des discussions et, si possible, des études de cas (apprentissage par résolution de problèmes par exemple).';
$string['tp_help'] = 'Travaux Pratiques non cliniques : Séances d’enseignement où les étudiants manipulent eux-mêmes les ressources pédagogiques (logiciels, microscopes, expé en labo, etc) sans manipulation d’animaux, d’organes ou de mannequins.';
$string['tpa_help'] = 'TP sur animaux sains : Séances d’enseignement où les étudiants travaillent eux-mêmes sur des animaux sains, des pièces anatomiques, des mannequins, des carcasses, etc. (par exemple : inspection ante mortem et post mortem, hygiène alimentaire, etc.). Toutes les activités VetSims sont incluses dans cette catégorie.';
$string['unsavedchanges'] = 'Vous avez des modifications non enregistrées. Voulez-vous fermer le formulaire sans enregistrer ?';
$string['usernotfound'] = 'Utilisateur non trouvé';
$string['value'] = 'Valeur';
$string['report:competency'] = 'Rapport des compétences Syllabus';
$string['report:discipline'] = 'Rapport des disciplines Syllabus';
