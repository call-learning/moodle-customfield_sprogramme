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

namespace customfield_sprogramme\local\persistent;

use core\persistent;
use lang_string;

/**
 * Class sprogramme
 *
 * @package    customfield_sprogramme
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sprogramme extends persistent {
    /**
     * Current table
     */
    const TABLE = 'customfield_sprogramme';

    /**
     * Return the custom definition of the properties of this model.
     *
     * Each property MUST be listed here.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'courseid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:courseid'),
            ],
            'moduleid' => [
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:moduleid'),
            ],
            'sortorder' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:sortorder'),
            ],
            'uc' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:uc'),
            ],
            'cct_ept' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:cct_ept'),
            ],
            'dd_rse' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:dd_rse'),
            ],
            'type_ae' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:type_ae'),
            ],
            'sequence' => [
                'default' => null,
                'null' => NULL_ALLOWED,
                'type' => PARAM_INT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:sequence'),
            ],
            'intitule_seance' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:intitule_seance'),
            ],
            'cm' => [
                'default' => null,
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:cm'),
            ],
            'td' => [
                'default' => null,
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:td'),
            ],
            'tp' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:tp'),
            ],
            'tpa' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:tpa'),
            ],
            'tc' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:tc'),
            ],
            'aas' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:aas'),
            ],
            'fmp' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:fmp'),
            ],
            'perso_av' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:perso_av'),
            ],
            'perso_ap' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_FLOAT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:perso_ap'),
            ],
            'consignes' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:consignes'),
            ],
            'supports' => [
                'default' => '',
                'null' => NULL_ALLOWED,
                'type' => PARAM_TEXT,
                'message' => new lang_string('invaliddata', 'customfield_sprogramme', 'sprogramme:supports'),
            ],
        ];
    }

    /**
     * Get the defintion of the table
     * @return array
     */
    public static function get_properties() {
        return self::define_properties();
    }

    /**
     * Get all records for a given course
     * @param int $courseid
     * @return array
     */
    public static function get_all_records_for_course(int $courseid): array {
        return self::get_records(['courseid' => $courseid], 'sortorder');
    }

    /**
     * Get all records for a given module
     * @param int $moduleid
     * @return array
     */
    public static function get_all_records_for_module(int $moduleid): array {
        return self::get_records(['moduleid' => $moduleid], 'sortorder');
    }

    /**
     * Delete dependencies
     *
     * @param bool $result
     * @return void
     */
    protected function after_delete($result) {
        if (!$result) {
            return;
        }
        $disciplines = sprogramme_disc::get_records(['pid' => $this->raw_get('id')]);
        foreach ($disciplines as $disc) {
            $disc->delete();
        }
    }

    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for tc
     * record the name of the set field in type_ae
     * @param mixed $tc
     */
    protected function set_tc($tc) {
        if ($tc) {
            $this->set('type_ae', 'tc');
        }
        // Change the value to null if it is 0
        $tc = $tc == 0 ? null : $tc;
        $this->raw_set('tc', $tc);
    }

    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for tpa
     * record the name of the set field in type_ae
     * @param mixed $tpa
     */
    protected function set_tpa($tpa) {
        if ($tpa) {
            $this->set('type_ae', 'tpa');
        }
        $tpa = $tpa == 0 ? null : $tpa;
        $this->raw_set('tpa', $tpa);
    }
    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for tp
     * record the name of the set field in type_ae
     * @param mixed $tp
     */
    protected function set_tp($tp) {
        if ($tp) {
            $this->set('type_ae', 'tp');
        }
        $tp = $tp == 0 ? null : $tp;
        $this->raw_set('tp', $tp);
    }
    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for td
     * record the name of the set field in type_ae
     * @param mixed $td
     */
    protected function set_td($td) {
        if ($td) {
            $this->set('type_ae', 'td');
        }
        $td = $td == 0 ? null : $td;
        $this->raw_set('td', $td);
    }
    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for cm
     * record the name of the set field in type_ae
     * @param mixed $cm
     */
    protected function set_cm($cm) {
        if ($cm) {
            $this->set('type_ae', 'cm');
        }
        $cm = $cm == 0 ? null : $cm;
        $this->raw_set('cm', $cm);
    }
    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for aas
     * record the name of the set field in type_ae
     * @param mixed $aas
     */
    protected function set_aas($aas) {
        if ($aas) {
            $this->set('type_ae', 'aas');
        }
        $aas = $aas == 0 ? null : $aas;
        $this->raw_set('aas', $aas);
    }
    /**
     * Set the type_ae based on the float fields cm, td, tp, tpa, tc, aas, fmp
     * This one is for fmp
     * record the name of the set field in type_ae
     * @param mixed $fmp
     */
    protected function set_fmp($fmp) {
        if ($fmp) {
            $this->set('type_ae', 'fmp');
        }
        $fmp = $fmp == 0 ? null : $fmp;
        $this->raw_set('fmp', $fmp);
    }

    


    /**
     * Hook to execute after a create.
     *
     * As situations are visible when the user (student) belongs to one of the groups, we need to make
     * sure that we send an event that will be observed so we clear the cache
     * @param object $data
     *
     * @return void
     */
    public function after_create_custom($data) {
        $disciplines = $data->disciplines;
        $competencies = $data->competencies;
        $pid = $this->raw_get('id');
        foreach ($disciplines as $discipline) {
            $disc = new sprogramme_disc(null, (object) [
                'pid' => $this->raw_get('id'),
                'did' => $discipline['did'],
                'discipline' => $discipline['name'],
                'percentage' => $discipline['percentage'],
            ]);
            $disc->save();
        }
        foreach ($competencies as $competency) {
            $comp = new sprogramme_comp(null, (object) [
                'pid' => $this->raw_get('id'),
                'cid' => $competency['cid'],
                'competency' => $competency['name'],
                'percentage' => $competency['percentage'],
            ]);
            $comp->save();
        }
    }
}
