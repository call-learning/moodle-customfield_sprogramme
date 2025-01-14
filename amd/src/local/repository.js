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
 * Gateway to the webservices.
 *
 * @module     customfield_sprogramme/local/repository
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

const disciplines = [
    {"id": 1, "number": 2, "name": "Immunology"},
    {"id": 2, "number": 2, "name": "Literacy & data management"},
    {"id": 3, "number": 2, "name": "Microbiology"},
    {"id": 4, "number": 2, "name": "Parasitology"},
    {"id": 5, "number": 2, "name": "Pathology"},
    {"id": 6, "number": 2, "name": "Pharma-cy-cology-cotherapy"},
    {"id": 7, "number": 2, "name": "Physiology"},
    {"id": 8, "number": 2, "name": "Prof. ethics & communication"},
    {"id": 9, "number": 2, "name": "Toxicology"},
    {"id": 10, "number": 3, "name": "CA_EQ Anesthesiology"},
    {"id": 11, "number": 3, "name": "CA_EQ Clinical pract training"},
    {"id": 12, "number": 3, "name": "CA_EQ Diagnostic imaging"},
    {"id": 13, "number": 3, "name": "CA_EQ Diagnostic pathology"},
    {"id": 14, "number": 3, "name": "CA_EQ Infectious diseases"},
    {"id": 15, "number": 3, "name": "CA_EQ Medecine"},
    {"id": 16, "number": 3, "name": "CA_EQ Preventive medicine"},
    {"id": 17, "number": 3, "name": "CA_EQ Repro & obstetrics"},
    {"id": 18, "number": 3, "name": "CA_EQ Surgery"},
    {"id": 19, "number": 3, "name": "CA_EQ Therapy"},
    {"id": 20, "number": 4, "name": "FPA Anesthesiology"},
    {"id": 21, "number": 4, "name": "FPA Clinical pract training"},
    {"id": 22, "number": 4, "name": "FPA Diagnostic imaging"},
    {"id": 23, "number": 4, "name": "FPA Diagnostic pathology"},
    {"id": 24, "number": 4, "name": "FPA Herd health management"},
    {"id": 25, "number": 4, "name": "FPA Husb, breeding & economics"},
    {"id": 26, "number": 4, "name": "FPA Infectious diseases"},
    {"id": 27, "number": 4, "name": "FPA Medecine"},
    {"id": 28, "number": 4, "name": "FPA Preventive medicine"},
    {"id": 29, "number": 4, "name": "FPA Repro & obstetrics"},
    {"id": 30, "number": 4, "name": "FPA Surgery"},
    {"id": 31, "number": 4, "name": "FPA Therapy"},
    {"id": 32, "number": 5, "name": "Control of food & feed"},
    {"id": 33, "number": 5, "name": "Food hygiene & environ. health"},
    {"id": 34, "number": 5, "name": "Food technology"},
    {"id": 35, "number": 5, "name": "Vet. legis & certification"},
    {"id": 36, "number": 5, "name": "Zoonoses"}
];

/**
 * Competvet repository class.
 */
class Repository {

    /**
     * Get JSON data
     * @param {Object} args The data to get.
     * @return {Promise} The promise.
     */
    getColumns(args) {
        const request = {
            methodname: 'customfield_sprogramme_get_columns',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Get the Table data.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    getData(args) {
        const request = {
            methodname: 'customfield_sprogramme_get_data',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Set the Table data.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    setData(args) {
        const request = {
            methodname: 'customfield_sprogramme_set_data',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Create a new row.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    createRow(args) {
        const request = {
            methodname: 'customfield_sprogramme_create_row',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Delete a row.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    deleteRow(args) {
        const request = {
            methodname: 'customfield_sprogramme_delete_row',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Get the list of disciplines.
     * @return {Array} The list of disciplines.
     */
    getDisciplines() {
        return disciplines;
    }
}

const RepositoryInstance = new Repository();

export default RepositoryInstance;
