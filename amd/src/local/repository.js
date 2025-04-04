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
     * Create a new module.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    createModule(args) {
        const request = {
            methodname: 'customfield_sprogramme_create_module',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Delete a module.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    deleteModule(args) {
        const request = {
            methodname: 'customfield_sprogramme_delete_module',
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
     * Update the sort order.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    updateSortOrder(args) {
        const request = {
            methodname: 'customfield_sprogramme_update_sort_order',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Get the CSV data for download.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    csvData(args) {
        const request = {
            methodname: 'customfield_sprogramme_csv_data',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Accept the changes.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    acceptRfc(args) {
        const request = {
            methodname: 'customfield_sprogramme_accept_rfc',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }
    /**
     * Reject the changes.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    rejectRfc(args) {
        const request = {
            methodname: 'customfield_sprogramme_reject_rfc',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }
    /**
     * Submit the changes.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    submitRfc(args) {
        const request = {
            methodname: 'customfield_sprogramme_submit_rfc',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }
    /**
     * Cancel the changes.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    cancelRfc(args) {
        const request = {
            methodname: 'customfield_sprogramme_cancel_rfc',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }
    /**
     * Remove the changes.
     * @param {Object} args The arguments.
     * @return {Promise} The promise.
     */
    removeRfc(args) {
        const request = {
            methodname: 'customfield_sprogramme_remove_rfc',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }
}

const RepositoryInstance = new Repository();

export default RepositoryInstance;
