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

namespace customfield_sprogramme\local\backup;

use restore_path_element;
use restore_structure_step;

/**
 * Restore task that provides all the restore process for the sprogramme customfield.
 *
 * @package   customfield_sprogramme
 * @copyright 2025 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_customfield_sprogramme_step extends restore_structure_step {
    /**
     * Process the customfield_module element.
     *
     * @param array $data
     */
    public function process_module(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $data->datafieldid = $this->get_mappingid('customfield', $data->datafieldid);
        $newitemid = $DB->insert_record('customfield_sprogramme_module', $data);
        $this->set_mapping('module', $oldid, $newitemid);
    }

    /**
     * Process the customfield_sprogramme element.
     *
     * @param array $data
     */
    public function process_sprogramme(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $data->moduleid = $this->get_mappingid('module', $data->moduleid);
        $data->datafieldid = $this->get_mappingid('customfield', $data->datafieldid);
        $newitemid = $DB->insert_record('customfield_sprogramme', $data);
        $this->set_mapping('sprogramme', $oldid, $newitemid);
    }

    /**
     * Process the customfield element.
     *
     * @param array $data
     */
    public function process_customfield(array $data): void {
    }

    /**
     * Process the customfield_sprogramme_discipline element.
     *
     * @param array $data
     */
    public function process_discipline(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $data->pid = $this->get_mappingid('sprogramme', $data->pid);
        $data->did = $this->get_mappingid('disciplineinfo', $data->did);
        $newitemid = $DB->insert_record('customfield_sprogramme_disc', $data);
        $this->set_mapping('discipline', $oldid, $newitemid);
    }

    /**
     * Process the customfield_sprogramme_competencies element.
     *
     * @param array $data
     */
    public function process_competency(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $data->pid = $this->get_mappingid('sprogramme', $data->pid);
        $data->cid = $this->get_mappingid('competencyinfo', $data->cid);
        $newitemid = $DB->insert_record('customfield_sprogramme_competencies', $data);
        $this->set_mapping('competency', $oldid, $newitemid);
    }

    /**
     * Process the customfield_sprogramme_disciplinelist element.
     *
     * @param array $data
     */
    public function process_disciplineinfo(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $existing = $DB->get_record('customfield_sprogramme_disclist', ['uniqueid' => $data->uniqueid]);
        if ($existing) {
            $this->set_mapping('disciplineinfo', $oldid, $existing->id);
            return;
        }
        $data->parent = $this->get_mappingid('disciplineinfo', $data->parent);
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $newitemid = $DB->insert_record('customfield_sprogramme_disclist', $data);
        $this->set_mapping('disciplineinfo', $oldid, $newitemid);
    }

    /**
     * Process the customfield_sprogramme_competencyinfo element.
     *
     * @param array $data
     */
    public function process_competencyinfo(array $data): void {
        global $DB;
        $data = (object) $data;
        $oldid = $data->id;
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $existing = $DB->get_record('customfield_sprogramme_complist', ['uniqueid' => $data->uniqueid]);
        if ($existing) {
            $this->set_mapping('competencyinfo', $oldid, $existing->id);
            return;
        }
        $data->parent = $this->get_mappingid('competencyinfo', $data->parent);
        $data->usermodified = $this->get_mappingid('user', $data->usermodified);
        $newitemid = $DB->insert_record('customfield_sprogramme_complist', $data);
        $this->set_mapping('competencyinfo', $oldid, $newitemid);
    }

    /**
     * Define the structure of the restore.
     *
     * @return array
     */
    protected function define_structure(): array {
        $customfield = new restore_path_element('customfield', '/course/customfields/customfield'); // So we can map old to new.
        $sprogramme = new restore_path_element('sprogramme', '/course/customfields/customfield/sprogrammes/sprogramme');
        $module = new restore_path_element('module', '/course/customfields/customfield/modules/module');
        $competencie = new restore_path_element(
            'competency',
            '/course/customfields/customfield/sprogrammes/sprogramme/competencies/competency'
        );
        $discipline = new restore_path_element(
            'discipline',
            '/course/customfields/customfield/sprogrammes/sprogramme/disciplines/discipline'
        );
        $disciplineinfos = new restore_path_element('disciplineinfo', '/course/disciplinelist/disciplineinfo');
        $competencyinfos = new restore_path_element('competencyinfo', '/course/competencylist/competencyinfo');
        return [$customfield, $sprogramme, $module, $competencie, $discipline, $disciplineinfos, $competencyinfos];
    }
}
