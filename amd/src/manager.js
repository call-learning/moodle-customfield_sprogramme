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
import Templates from 'core/templates';
import Notification from 'core/notification';
import {getString} from 'core/str';
import {debounce} from 'core/utils';
import './local/components/table';

/**
 * Manager class.
 * @class
 */
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
     * Get the available disciplines.
     * @return {Array} The disciplines.
     */
    getDisciplines() {
        const disciplines = Repository.getDisciplines();
        return disciplines;
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
                'disciplines': {
                    'id': 'id',
                    'name': 'name',
                    'percentage': 'percentage',
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
            // Clean the disciplines.
            cleanedRow.disciplines = row.disciplines.map(discipline => {
                const cleanedDiscipline = {};
                Object.keys(rowObject.rows.disciplines).forEach(key => {
                    cleanedDiscipline[key] = discipline[key];
                });
                return cleanedDiscipline;
            });
            return cleanedRow;
        });
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
        const response = await Repository.deleteRow({courseid: this.courseid, rowid: rowid});
        return new Promise((resolve) => {
            if (response) {
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
        const app = document.querySelector('.' + this.table);
        app.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
            }
        });
        // Listen to all changes in the table.
        app.addEventListener('change', (e) => {
            const input = e.target.closest('[data-input]');
            if (input) {
                this.change(input);
            }
        });
        // Listen to the arrow down and up keys to navigate to the next or previous row.
        app.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                this.navigate(e);
            }
        });
        // Disciplines form typeahead, using .disciplineform input[type="search"]
        // All options are already in the DOM, just show and hide them.
        // On select return the discipline id and add it in this form to the cell
        // {id: 1, value: 20}, {id: 2, value: 60}, {id: 7, value: 20}
        // The value is set to 100 for now, this will be updated later in this code by adding another input field.
        const form = document.querySelector('[data-region="disciplineform"]');
        const search = form.querySelector('input[type="search"]');
        search.addEventListener('input', (e) => {
            const input = e.target.closest('input');
            if (input) {
                this.typeahead(input);
            }
        });
    }

    /**
     * Typeahead.
     * Limit to 5 options.
     * @param {object} input The input that was changed.
     * @return {void}
     */
    typeahead(input) {
        const value = input.value;
        const form = document.querySelector('[data-region="disciplineform"]');
        const options = form.querySelectorAll('[data-option]');
        options.forEach(option => {
            this.removeMatchBold(option);
            if (option.textContent.toLowerCase().includes(value.toLowerCase())) {
                option.classList.remove('d-none');
                this.makeMatchBold(option, value);
            } else {
                option.classList.add('d-none');
            }
        });
    }

    /**
     * Make the match bold.
     * @param {object} option The option.
     * @param {string} value The value.
     * @return {void}
     */
    makeMatchBold(option, value) {
        const text = option.textContent;
        const index = text.toLowerCase().indexOf(value.toLowerCase());
        const first = text.slice(0, index);
        const match = text.slice(index, index + value.length);
        const last = text.slice(index + value.length);
        option.innerHTML = first + '<strong>' + match + '</strong>' + last;
    }

    /**
     * Remove the match bold.
     * @param {object} option The option.
     * @return {void}
     */
    removeMatchBold(option) {
        option.innerHTML = option.textContent;
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
        if (btn.dataset.action === 'adddisc') {
            this.showDisciplineForm(btn);
        }
        if (btn.dataset.action === 'removedisc') {
            this.removeDiscipline(btn);
        }
        if (btn.dataset.action === 'closedisciplineform') {
            this.closeDisciplineForm();
        }
        if (btn.dataset.action === 'selectdiscipline') {
            const option = btn.closest('[data-option]');
            const discipline = {
                id: option.dataset.id,
                name: option.textContent,
            };
            this.setDisciplineForm(discipline);
        }
        if (btn.dataset.action === 'discipline-confirm') {
            this.addDiscipline();
        }
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
     * Add a discipline to the row.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async showDisciplineForm(btn) {
        const row = btn.closest('[data-row]');
        const form = document.querySelector('[data-region="disciplineform"]');
        form.querySelector('#rowid').value = row.dataset.index;
        const arrow = form.querySelector('.formarrow');


        // Get the row index nr based on the row position in the table.
        const rows = document.querySelectorAll('[data-region="rows"] [data-row]');
        const rowArray = Array.from(rows);
        const index = rowArray.indexOf(row);
        // Set the title of the form to show the row number.
        form.querySelector('[data-region="rownumber"]').textContent =
            await getString('row', 'customfield_sprogramme', index + 1);

        // Attache the form to the first row for the first 8 rows.
        // Then attach it to 8 rows before the clicked row.
        // This makes sure the form is always visible.
        const setindex = index - 8;
        let attachTo;
        let attachToButton;
        if (setindex > 0) {
            attachTo = rowArray[setindex].querySelector('[data-disciplines]');
            attachToButton = rowArray[setindex].querySelector('[data-action="adddisc"]');
        } else {
            attachTo = rowArray[0].querySelector('[data-disciplines]');
            attachToButton = rowArray[0].querySelector('[data-action="adddisc"]');
        }
        attachTo.appendChild(form);

        // Position the form arrow next to the button that was clicked.
        const rectBtn = btn.getBoundingClientRect();
        const rectAttachToButton = attachToButton.getBoundingClientRect();
        arrow.style.top = rectBtn.top - rectAttachToButton.top + 'px';
        this.renderFormDisciplines(row.dataset.index);
    }

    /**
     * Remove the discipline form.
     * @return {void}
     */
    closeDisciplineForm() {
        const container = document.querySelector('[data-region="disciplineform-container"]');
        const form = document.querySelector('[data-region="disciplineform"]');
        container.appendChild(form);
    }

    /**
     * Select a discipline.
     * @param {object} discipline The discipline.
     * @return {void}
     */
    async setDisciplineForm(discipline) {
        const form = document.querySelector('[data-region="disciplineform"]');
        const formFieldSearch = form.querySelector('input[type="search"]');
        const formFieldValue = form.querySelector('#discipline-value');
        const formFieldDiscipline = form.querySelector('#discipline-id');
        const formFieldDisciplineName = form.querySelector('#discipline-name');
        const formFieldLastIds = form.querySelector('#lastids');

        formFieldDiscipline.value = discipline.id;
        formFieldDisciplineName.value = discipline.name;
        formFieldSearch.value = discipline.name;

        // Add the discipline id to the formFieldLastIds.
        const lastIds = formFieldLastIds.value.split(',');
        if (!lastIds.includes(discipline.id)) {
            lastIds.push(discipline.id);
            formFieldLastIds.value = lastIds.join(',');
        }
        formFieldValue.focus();
    }

    // Add a discipline to the row.
    async addDiscipline() {
        const form = document.querySelector('[data-region="disciplineform"]');
        const rowid = form.querySelector('#rowid').value;
        const disciplineid = form.querySelector('#discipline-id').value;
        const disciplinevalue = form.querySelector('#discipline-value').value;
        const disciplinename = form.querySelector('#discipline-name').value;
        const discipline = {
            id: disciplineid,
            name: disciplinename,
            percentage: disciplinevalue,
        };
        const rows = State.getValue('rows');
        const row = rows.find(r => r.id == rowid);
        // Update or add the discipline to the row.
        const disciplineIndex = row.disciplines.findIndex(d => d.id == discipline.id);
        const container = document.querySelector(
            '[data-disciplines][data-rowid="' + rowid + '"] [data-region="container-disciplines"]');
        const selectedcontainer = form.querySelector('[data-region="selected-disciplines"]');
        if (disciplineIndex > -1) {
            row.disciplines[disciplineIndex] = discipline;
            const rendered = container.querySelector('[data-id="' + discipline.id + '"]');
            const selected = selectedcontainer.querySelector('[data-id="' + discipline.id + '"]');
            const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/discipline', discipline);
            await Templates.replaceNode(rendered, html, js);
            await Templates.replaceNode(selected, html, js);

        } else {
            row.disciplines.push(discipline);
            const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/discipline', discipline);
            await Templates.appendNodeContents(container, html, js);
            await Templates.appendNodeContents(selectedcontainer, html, js);
        }
        this.setTableData();
    }

    /**
     * Render the disciplines in the form.
     * @param {int} rowid The rowid.
     * @return {void}
     */
    async renderFormDisciplines(rowid) {
        const rows = State.getValue('rows');
        const row = rows.find(r => r.id == rowid);
        const disciplines = row.disciplines;
        const form = document.querySelector('[data-region="disciplineform"]');
        const container = form.querySelector('[data-region="selected-disciplines"]');
        container.innerHTML = '';
        disciplines.forEach(async(discipline) => {
            const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/discipline', discipline);
            Templates.appendNodeContents(container, html, js);
        });
    }

    /**
     * Remove a discipline from the row.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async removeDiscipline(btn) {
        const form = document.querySelector('[data-region="disciplineform"]');
        const rowid = form.querySelector('#rowid').value;
        const disciplineid = btn.closest('[data-id]').dataset.id;
        const rows = State.getValue('rows');
        const row = rows.find(r => r.id == rowid);
        const index = row.disciplines.findIndex(d => d.id == disciplineid);
        row.disciplines.splice(index, 1);
        const container = document.querySelector(
            '[data-disciplines][data-rowid="' + rowid + '"] [data-region="container-disciplines"]');
        const selectedcontainer = document.querySelector('[data-region="selected-disciplines"]');
        const discipline = container.querySelector('[data-id="' + disciplineid + '"]');
        const selected = selectedcontainer.querySelector('[data-id="' + disciplineid + '"]');
        container.removeChild(discipline);
        selectedcontainer.removeChild(selected);
        this.setTableData();
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
            Json.forEach(column => {
                column[column.type] = true;
            });
            return Json;
        } catch (error) {
            return;
        }
    }

    /**
     * Navigate to the next or previous row and left or right column.
     * @param {Event} e The event.
     * @return {void}
     */
    navigate(e) {
        const currentIndex = e.target.closest('[data-row]').dataset.index;
        const currentColumn = e.target.closest('[data-cell]').dataset.columnid;
        const allRows = document.querySelectorAll('[data-row]');
        for (let i = 0; i < allRows.length; i++) {
            if (allRows[i].dataset.index == currentIndex) {
                if (e.key === 'ArrowDown' && i < allRows.length - 1) {
                    const nextInput = allRows[i + 1].querySelector(`[data-columnid="${currentColumn}"]`);
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
                if (e.key === 'ArrowUp' && i > 0) {
                    const previousInput = allRows[i - 1].querySelector(`[data-columnid="${currentColumn}"]`);
                    if (previousInput) {
                        previousInput.focus();
                    }
                }
            }
        }
        // This part is not working yet, it might not be accessible.
        if (e.key === 'ArrowRight') {
            const nextColumn = e.target.closest('[data-cell]').nextElementSibling;
            if (nextColumn) {
                nextColumn.focus();
            }
        }
        if (e.key === 'ArrowLeft') {
            const previousColumn = e.target.closest('[data-cell]').previousElementSibling;
            if (previousColumn) {
                previousColumn.focus();
            }
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