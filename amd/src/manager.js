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
 * TODO describe module manager
 *
 * @module     customfield_sprogramme/manager
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import State from 'customfield_sprogramme/local/state';
import Repository from 'customfield_sprogramme/local/repository';
import Notification from 'core/notification';
import {debounce} from 'core/utils';
import './local/components/table';


class Manager {

    /**
     * Row number.
     */
    rowNumber = 0;

    /**
     * The courseid.
     * @type {Number}
     */
    courseid;

    /**
     * The table name.
     */
    table = 'customfield_sprogramme';

    /**
     * Constructor.
     * @param {String} courseid The courseid.
     * @return {void}
     */
    constructor(courseid) {
        this.courseid = parseInt(courseid);
        this.addEventListeners();
        this.getDatagrid();
    }

    async getDatagrid() {
        await this.getTableConfig();
        await this.getTableData();
    }

    /**
     * Get the table configuration.
     * @return {Promise} The promise.
     */
    async getTableConfig() {
        try {
            const response = await Repository.getColumns({table: this.table});
            // Validate the response, the response.date should be a string that can be parsed to a JSON object.
            const json = this.parseResponse(response);
            if (json) {
                await State.setValue('columns', json);
            } else {
                Notification.exception('The response is not valid JSON');
            }
        } catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Get the table data.
     * @return {void}
     */
    async getTableData() {
        try {
            const response = await Repository.getData({courseid: this.courseid});
            // Validate the response, the response.date should be a string that can be parsed to a JSON object.
            if (response.rows.length > 0) {
                const rows = this.parseRows(response.rows);
                State.setValue('rows', rows);
            } else {
                const row = await this.createRow(0);
                State.setValue('rows', [row]);
                this.resetRowSortorder();
            }
        } catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Parse the rows, add the correct column properties to each cell.
     * @param {Array} rows The rows.
     * @return {Array} The parsed rows.
     */
    parseRows(rows) {
        const columns = State.getValue('columns');
        return rows.map(row => {
            row.cells = row.cells.map(cell => {
                const column = columns.find(column => column.column == cell.column);
                // Clone the column properties to the cell but keep the cell properties.
                cell = Object.assign({}, cell, column);
                cell[cell.type] = true;
                cell.edit = true;
                return cell;
            });
            return row;
        });
    }

    /**
     * Get the row object that can be accepted by the webservice.
     * @return {Array} The keys.
     */
    getRowObject() {
        return {
            'rows': {
                'id': 'id',
                'sortorder': 'sortorder',
                'cells': {
                    'type': 'type',
                    'column': 'column',
                    'value': 'value',
                },
            },
        };
    }

    /**
     * Clean the rows object.
     * @param {Array} rows The rows.
     * @return {Array} The cleaned rows.
     */
    cleanRows(rows) {
        const rowObject = this.getRowObject();
        const cleanedRows = rows.map(row => {
            const cleanedRow = {};
            Object.keys(rowObject.rows).forEach(key => {
                cleanedRow[key] = row[key];
            });
            // Clean the cells.
            cleanedRow.cells = row.cells.map(cell => {
                const cleanedCell = {};
                Object.keys(rowObject.rows.cells).forEach(key => {
                    cleanedCell[key] = cell[key];
                });
                return cleanedCell;
            });
            return cleanedRow;
        });
        window.console.log(JSON.stringify(cleanedRows));
        return cleanedRows;
    }


    /**
     * Set the table data.
     * @return {void}
     */
    async setTableData() {
        const set = debounce(async() => {
            try {
                const rows = State.getValue('rows');
                const response = await Repository.setData({courseid: this.courseid, rows: this.cleanRows(rows)});
                if (!response) {
                    Notification.exception('No response from the server');
                }
            } catch (error) {
                Notification.exception('Error 2' + error);
            }
        }, 600);
        set();
    }

    /**
     * Create a new row.
     * @param {Number} index The index.
     * @return {Promise} The promise.
     */
    async createRow(index) {
        const rowid = await Repository.createRow({courseid: this.courseid, sortorder: index});
        return new Promise((resolve) => {
            const row = {};
            row.id = rowid;
            row.sortorder = index;
            const columns = State.getValue('columns');
            if (columns === undefined) {
                resolve();
                return;
            }
            // The copy the columns to the row and call them cells.
            row.cells = columns.map(column => structuredClone(column));
            // Set the correct types for the cells.
            row.cells.forEach(cell => {
                cell.edit = true;
                cell.value = '';
                cell[cell.type] = true;
            });
            resolve(row);
        });
    }

    /**
     * Delete a row.
     * @param {Object} btn The button that was clicked.
     * @return {Promise} The promise.
     */
    async delete(btn) {
        const rowid = btn.closest('[data-row]').dataset.index;
        const response = await Repository.deleteRow({rowid: rowid});
        return new Promise((resolve) => {
            if (response.success) {
                const rows = State.getValue('rows');
                const index = Array.from(btn.closest('[data-region="rows"]').children).indexOf(btn.closest('[data-row]'));
                rows.splice(index, 1);
                this.resetRowSortorder();
                State.setValue('rows', rows);
            }
            resolve(rowid);
        });
    }

    /**
     * Reset the row sortorder values.
     * @return {void}
     */
    resetRowSortorder() {
        const rows = State.getValue('rows');
        rows.forEach((row, index) => {
            row.sortorder = index;
        });
        State.setValue('rows', rows);
    }

    /**
     * Add event listeners.
     * @return {void}
     */
    addEventListeners() {
        document.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
            }
        });
        // Listen to all changes in the table.
        document.addEventListener('change', (e) => {
            const input = e.target.closest('[data-input]');
            if (input) {
                this.change(input);
            }
        });
    }

    /**
     * Actions.
     * @param {object} btn The button that was clicked.
     */
    actions(btn) {
        if (btn.dataset.action === 'add') {
            this.add(btn);
        }
        if (btn.dataset.action === 'edit') {
            this.edit(btn);
        }
        if (btn.dataset.action === 'save') {
            this.save();
            this.stopEdit();
        }
        if (btn.dataset.action === 'delete') {
            this.delete(btn);
        }

        this.setTableData();
    }

    /**
     * Change.
     * @param {object} input The input that was changed.
     */
    change(input) {
        const row = input.closest('[data-row]');
        const cell = input.closest('[data-cell]');
        const value = input.value;
        const columnid = cell.dataset.columnid;
        const index = row.dataset.index;
        const rows = State.getValue('rows');
        // Find the correct cell in the row.
        const rowIndex = rows.findIndex(r => r.id == index);
        const cellIndex = rows[rowIndex].cells.findIndex(c => c.columnid == columnid);
        rows[rowIndex].cells[cellIndex].value = value;
        this.setTableData();
    }

    /**
     * Inject a new row after this row.
     * @param {object} btn The button that was clicked.
     */
    async add(btn) {
        const rows = State.getValue('rows');
        // Find the rowcount this button is in the table.
        const tablerows = document.querySelectorAll('[data-region="rows"] [data-row]');
        const index = Array.from(tablerows).indexOf(btn.closest('[data-row]'));
        const row = await this.createRow(index + 1);
        rows.splice(index + 1, 0, row);
        this.resetRowSortorder();
        State.setValue('rows', rows);
    }

    /**
     * Parse response data to a JSON object.
     * @param {Object} response The response.
     * @return {Any} The JSON object.
     */
    parseResponse(response) {
        if (typeof response.data !== 'string') {
            return;
        }
        try {
            const Json = JSON.parse(response.data);
            return Json;
        } catch (error) {
            return;
        }
    }
}

/*
 * Initialise
 * @param {String} courseid The courseid.
 */
const init = (courseid) => {
    new Manager(courseid);
};

export default {
    init: init,
};