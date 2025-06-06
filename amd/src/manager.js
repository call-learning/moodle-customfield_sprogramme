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
     * Module number.
     */
    moduleNumber = 0;

    /**
     * The courseid.
     * @type {Number}
     */
    courseid;

    /**
     * The element.
     * @type {HTMLElement}
     */
    element;

    /**
     * The table name.
     */
    table = 'customfield_sprogramme';

    /**
     * The table columns.
     * @type {Array}
     */
    columns = [];

    /**
     * Constructor.
     * @param {HTMLElement} element The element.
     * @param {String} courseid The courseid.
     * @return {void}
     */
    constructor(element, courseid) {
        this.element = element;
        if (this.element.dataset.inintialized) {
            return;
        }
        this.courseid = parseInt(courseid);
        this.addEventListeners();
        this.getTableData();
        this.element.dataset.inintialized = true;
    }

    /**
     * Add event listeners.
     * @return {void}
     */
    addEventListeners() {
        const form = document.querySelector('[data-region="app"]');
        form.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
            } else {
                const activetds = form.querySelectorAll('[data-action="showchanges"]');
                activetds.forEach(td => {
                    td.classList.remove('active');
                });
            }
        });
        // Listen to all changes in the table.
        form.addEventListener('change', (e) => {
            const input = e.target.closest('[data-input="auto"]');
            if (input) {
                this.change(input);
            }
            const modulename = e.target.closest('[data-region="modulename"]');
            if (modulename) {
                this.changeModule(modulename);
            }
        });

        // Listen to the arrow down and up keys to navigate to the next or previous row.
        form.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                this.navigate(e);
                e.preventDefault();
            }
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
        });

        let dragging = null;
        let isDraggingAllowed = false;
        form.addEventListener('mousedown', (e) => {
            if (e.target.closest('[data-region="dragicon"]')) {
                isDraggingAllowed = true;
            } else {
                isDraggingAllowed = false;
            }
        });

        form.addEventListener('dragstart', (e) => {
            if (!isDraggingAllowed) {
                e.preventDefault();
            }
            if (e.target.tagName === 'TR') {
                dragging = e.target;
                e.target.effectAllowed = 'move';
            }
        });
        form.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('tr');
            if (target && target !== dragging && target.parentNode.dataset.region === 'rows') {
                const rect = target.getBoundingClientRect();
                if (e.clientY - rect.top > rect.height / 2) {
                    target.parentNode.insertBefore(dragging, target.nextSibling);
                } else {
                    target.parentNode.insertBefore(dragging, target);
                }
            }
        });
        form.addEventListener("drop", (e) => {
            e.preventDefault(); // Voorkom standaard drop-actie
        });
        form.addEventListener('dragend', (e) => {
            const rowId = dragging.dataset.index;
            const prevRowId = dragging.previousElementSibling ? dragging.previousElementSibling.dataset.index : 0;
            const moduleId = dragging.closest('[data-region="module"]').dataset.id;
            this.moveRow(parseInt(moduleId), parseInt(rowId), prevRowId ? parseInt(prevRowId) : null);
            dragging = null;
            e.preventDefault(); // Voorkom standaard drop-actie
        });
        // Disciplines form typeahead, using .disciplineform input[type="search"]
        // All options are already in the DOM, just show and hide them.
        // On select return the discipline id and add it in this form to the cell
        // {id: 1, value: 20}, {id: 2, value: 60}, {id: 7, value: 20}
        // The value is set to 100 for now, this will be updated later in this code by adding another input field.
        const disciplineForm = document.querySelector('[data-region="disciplineform"]');
        const search = disciplineForm.querySelector('input[type="search"]');
        search.addEventListener('input', (e) => {
            const input = e.target.closest('input');
            if (input) {
                this.typeahead(input);
            }
        });

        // Listen for the saveconfirm custom event. When run save the table data.
        document.addEventListener('saveconfirm', () => {
            this.setTableData();
        });
    }

    /**
     * Get the table data.
     * @return {void}
     */
    async getTableData() {
        try {
            const response = await Repository.getData({courseid: this.courseid, showrfc: 1});
            // Validate the response, the response.date should be a string that can be parsed to a JSON object.
            if (response.modules.length > 0) {
                const modules = this.parseModules(response.modules);
                const columns = modules[0].columns;
                // Now clone this columns array to the state.

                State.setValue('columns', [...columns]);
                State.setValue('modules', modules);
                State.setValue('rfcs', response.rfcs);
            } else {
                this.addModule();
                this.getTableData();
            }
        } catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Parse the modules, add the correct column properties to each cell.
     * @param {Array} modules The modules.
     * @return {Array} The parsed rows.
     */
    parseModules(modules) {
        modules.forEach(mod => {
            mod.rows.map(row => {
                row.cells = row.cells.map(cell => {
                    const column = mod.columns.find(column => column.column == cell.column);
                    // Clone the column properties to the cell but keep the cell properties.
                    cell = Object.assign({}, cell, column);
                    if (cell.type === 'select') {
                        // Clone the options array to avoid shared references
                        cell.options = cell.options.map(option => {
                            const clonedOption = Object.assign({}, option);
                            if (clonedOption.name == cell.value) {
                                clonedOption.selected = true;
                            }
                            return clonedOption;
                        });
                    }
                    cell.edit = true;
                    return cell;
                });
                return row;
            });
        });
        return modules;
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
                'deleted': 'false',
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
                'competencies': {
                    'id': 'id',
                    'name': 'name',
                    'percentage': 'percentage',
                },
            },
        };
    }

    /**
     * Check the cell value. It can not exceed the cell length.
     * @param {object} cell The cell.
     * @return {void}
     */
    checkCellValue(cell) {
        if (cell.value === null) {
            return;
        }
        if (cell.type === 'text' && cell.value.length > cell.length) {
            cell.value = cell.value.substring(0, cell.length);
        }
    }

    /**
     * Clean a single cell.
     * @param {object} cell The cell to clean.
     * @param {Array} cellKeys The keys to keep in the cell.
     */
    cleanCell(cell, cellKeys) {
        const cleaned = {};
        this.checkCellValue(cell);
        cellKeys.forEach(key => {
            cleaned[key] = cell[key];
        });
        return cleaned;
    }

    /**
     * Clean a list of objects based on allowed keys.
     * @param {Array} items The items to clean.
     * @param {Array} allowedKeys The keys to keep in the items.
     */
    cleanList(items, allowedKeys) {
        return items.map(item => {
            const cleaned = {};
            allowedKeys.forEach(key => {
                cleaned[key] = item[key];
            });
            return cleaned;
        });
    }

    /**
     * Clean the Modules array.
     * @param {Array} modules The modules.
     * @return {Array} The cleaned modules.
     */
    cleanModules(modules) {
        const rowSpec = this.getRowObject().rows;

        return modules.map(module => {
            const cleanedModule = {
                id: module.moduleid,
                name: module.modulename,
                sortorder: module.modulesortorder,
                deleted: module.deleted || false,
                rows: module.rows.map(row => {
                    const cleanedRow = {
                        id: row.id,
                        sortorder: row.sortorder,
                        deleted: row.deleted || false,
                        cells: this.cleanList(row.cells, Object.keys(rowSpec.cells)),
                        disciplines: this.cleanList(row.disciplines, Object.keys(rowSpec.disciplines)),
                        competencies: this.cleanList(row.competencies, Object.keys(rowSpec.competencies)),
                    };
                    return cleanedRow;
                }),
            };
            return cleanedModule;
        });
    }

    /**
     * Set the table data.
     * @return {void}
     */
    async setTableData() {
        const set = debounce(async() => {
            const saveConfirmButton = document.querySelector('[data-action="saveconfirm"]');
            const warnings = document.querySelector('[data-region="sprogramme-warnings"]');
            saveConfirmButton.classList.add('saving');
            const modules = State.getValue('modules');
            const cleanedModules = this.cleanModules(modules);
            const response = await Repository.setData({courseid: this.courseid, modules: cleanedModules});
            if (!response) {
                Notification.exception('No response from the server');
            } else {
                if (response.data == 'rfclocked') {
                    warnings.innerHTML = await getString('rfclocked', 'customfield_sprogramme');
                    warnings.classList.remove('d-none');
                } else {
                    warnings.innerHTML = '';
                    warnings.classList.add('d-none');
                }
                this.getTableData();
                const modulesStatic = State.getValue('modules');
                State.setValue('modulesstatic', modulesStatic);
            }
            setTimeout(() => {
                saveConfirmButton.classList.remove('saving');
            }, 200);
        }, 600);
        set();
    }

    /**
     * Actions.
     * @param {object} btn The button that was clicked.
     */
    actions(btn) {
        const actionMap = {
            'addrow': this.addRow,
            'deleterow': this.deleteRow,
            'addmodule': this.addModule,
            'deletemodule': this.deleteModule,
            'adddisc': this.showDisciplineForm,
            'addcomp': this.showDisciplineForm,
            'removedisc': this.removeDiscipline,
            'closedisciplineform': this.closeDisciplineForm,
            'selectdiscipline': (btn) => {
                const option = btn.closest('[data-option]');
                const discipline = {
                    id: option.dataset.id,
                    name: option.textContent,
                };
                this.setDisciplineForm(discipline);
            },
            'discipline-confirm': this.addDiscipline,
            'saveconfirm': this.setTableData,
            'loaddiscipline': this.loadDiscipline,
            'showchanges': this.showchanges,
            'acceptrfc': this.acceptRfc,
            'rejectrfc': this.rejectRfc,
            'submitrfc': this.submitRfc,
            'cancelrfc': this.cancelRfc,
            'removerfc': this.removeRfc,
            'augmenttable': this.augmentTable,
            'resetrfc': this.resetRfc,
        };
        const action = btn.dataset.action;
        if (actionMap[action]) {
            actionMap[action].call(this, btn);
        }
    }

    /**
     * Inject a new row after this row.
     * @param {object} btn The button that was clicked.
     */
    async addRow(btn) {
        const modules = State.getValue('modules');

        let rowid = btn.dataset.id;
        const moduleid = btn.closest('[data-region="module"]').dataset.id;
        const module = modules.find(m => m.moduleid == moduleid);
        const rows = module.rows;
        // When called from the link under the table, the rowid is not set.
        if (rowid == -1) {
            rowid = rows[rows.length - 1].id;
        }

        const row = await this.createRow();
        if (!row) {
            return;
        }
        // Inject the row after the clicked row.
        rows.splice(rows.indexOf(rows.find(r => r.id == rowid)) + 1, 0, row);
        this.resetRowSortorder();
        State.setValue('modules', modules);
    }

    /**
     * Create a new row.
     *
     * @return {Object} The row object.
     */
     createRow() {

        const row = {};
        row.id = this.rowNumber - 1;
        const columns = State.getValue('columns');
        // The copy the columns to the row and call them cells.
        row.cells = columns.map(column => structuredClone(column));
        // Set the correct types for the cells.
        row.cells.forEach(cell => {
            cell.edit = true;
            cell.value = '';
            cell[cell.type] = true;
        });
        row.disciplines = [];
        row.competencies = [];
        return row;
    }

    /**
     * Delete a row.
     * @param {Object} btn The button that was clicked.
     * @return {Promise} The promise.
     */
    async deleteRow(btn) {
        const modules = State.getValue('modules');
        const rowid = parseInt(btn.closest('[data-row]').dataset.index);
        const moduleid = parseInt(btn.closest('[data-region="module"]').dataset.id);
        const modulefound = modules.find(m => m.moduleid == moduleid);
        if (modulefound.rows.length > 0) {
            // Find the row in the module.
            const rowIndex = modulefound.rows.findIndex(r => r.id == rowid);
            if (rowIndex !== -1) {
                // Add the deleted attribute to the row.
                modulefound.rows[rowIndex].deleted = true;
                State.setValue('modules', modules);
            } else {
                Notification.exception('Row not found');
            }
        }
    }

    /**
     * Change.
     * @param {object} input The input that was changed.
     */
    change(input) {
        const row = input.closest('[data-row]');
        const cell = input.closest('[data-cell]');
        const group = input.dataset.group;
        const value = input.value;
        const columnid = parseInt(cell.dataset.columnid);
        const index = parseInt(row.dataset.index);
        const modules = State.getValue('modules');
        modules.forEach(module => {
            // Find the correct cell in the row.
            const rowIndex = module.rows.findIndex(r => r.id == index);
            if (rowIndex === -1) {
                return;
            }
            const cellIndex = module.rows[rowIndex].cells.findIndex(c => c.columnid == columnid);
            module.rows[rowIndex].cells[cellIndex].value = value;
            // Find the other cells with the same group and null the value.
            if (group) {
                module.rows[rowIndex].cells.forEach(c => {
                    if (c.group === group && c.columnid !== columnid) {
                        c.value = null;
                        row.querySelector(`[data-columnid="${c.columnid}"] input`).value = null;
                    }
                });
            }
        });
        this.sumtotals();
        // this.setTableData();
    }

    /**
     * Sumtotals.
     * Sum all columns.
     */
    sumtotals() {
        const form = document.querySelector('[data-region="app"]');
        const columns = form.querySelectorAll('[data-region="sumtotals"]');
        columns.forEach(column => {
            const columnid = column.dataset.columnid;
            let sum = 0;
            let containsRfc = false;
            const inputs = form.querySelectorAll(`[data-columnid="${columnid}"] input`);
            inputs.forEach(input => {
                if (input.value) {
                    sum += parseFloat(input.value);
                    if (input.dataset.rfcstate === '1') {
                        containsRfc = true;
                    }
                }
            });
            sum = sum ? sum : '';
            column.innerHTML = sum;
            if (containsRfc) {
                column.classList.add('rfc');
                column.innerHTML = sum ? sum : 0;
            }
        });
    }

    /**
     * Change the module name.
     * @param {object} input The input that was changed.
     * @return {void}
     */
    changeModule(input) {
        const module = input.closest('[data-region="module"]');
        const moduleid = module.dataset.id;
        const name = input.value;
        const modules = State.getValue('modules');
        modules.forEach(module => {
            if (module.moduleid == moduleid) {
                module.modulename = name;
            }
        });
        // this.setTableData();
    }

    /**
     * Delete a module.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async deleteModule(btn) {
        const moduleid = btn.closest('[data-region="module"]').dataset.id;
        const modules = State.getValue('modules');
        const moduleIndex = modules.findIndex(m => m.moduleid == moduleid);
        if (moduleIndex !== -1) {
            // Add the deleted attribute to the module.
            modules[moduleIndex].deleted = true;
            State.setValue('modules', modules);
        }
    }

    /**
     * Create a new module.
     * @return {Integer} The module id.
     */
    async createModule() {
        return this.moduleNumber--;
    }

    /**
     * Add a new module.
     * @return {void}
     */
    async addModule() {
        const modules = State.getValue('modules');
        const moduleid = await this.createModule();
        const row = this.createRow();
        const module = {
            moduleid: moduleid,
            modulename: ' ',
            rows: [row],
        };
        modules.push(module);
        this.resetRowSortorder();
        State.setValue('modules', modules);
    }

    /**
     * Get the row from the state.
     * @param {int} rowid The rowid.
     */
    getRow(rowid) {
        const modules = State.getValue('modules');
        // Combine all rows in one array.
        const rows = modules.reduce((acc, module) => {
            return acc.concat(module.rows);
        }, []);
        const row = rows.find(r => r.id == rowid);
        return row;
    }

    /**
     * Move a row within a module to a new position, based on previd.
     * @param {Number} moduleId The module to update.
     * @param {Number} rowId The row to move.
     * @param {Number|null} prevRowId The row after which the moved row should appear. Null means move to top.
     */
    moveRow(moduleId, rowId, prevRowId) {
        const modules = State.getValue('modules');
        const module = modules.find(m => m.moduleid === moduleId);
        if (!module) {
            return;
        }

        const rows = module.rows;
        const rowIndex = rows.findIndex(r => r.id === rowId);
        if (rowIndex === -1) {
            return;
        }

        // Remove the row from its current position
        const [rowToMove] = rows.splice(rowIndex, 1);

        // Find index to insert after
        let insertIndex = 0;
        if (prevRowId !== null) {
            const prevIndex = rows.findIndex(r => r.id === prevRowId);
            insertIndex = prevIndex + 1;
        }

        // Insert the row
        rows.splice(insertIndex, 0, rowToMove);

        // Reset sortorders
        rows.forEach((row, index) => {
            row.sortorder = index + 1;
        });

        // Update the state
        State.setValue('modules', modules);
    }

    /**
     * Reset the row sortorder values.
     * @return {void}
     */
    resetRowSortorder() {
        const modules = State.getValue('modules');
        modules.forEach((module, mindex) => {
            module.modulesortorder = mindex;
            module.rows.forEach((row, index) => {
                row.sortorder = index;
            });
        });
        State.setValue('modules', modules);
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
     * Add a discipline to the row.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async showDisciplineForm(btn) {
        const btnAction = btn.dataset.action;
        const region = btnAction === 'adddisc' ? 'data-disciplines' : 'data-competencies';
        const row = btn.closest('[data-row]');
        const module = btn.closest('[data-region="module"]');
        const form = document.querySelector('[data-region="disciplineform"]');
        form.classList.remove('data-disciplines', 'data-competencies');
        form.classList.add(region);
        form.querySelector('#rowid').value = row.dataset.index;
        const arrow = form.querySelector('.formarrow');
        form.querySelector('#discipline-value').value = '';
        form.querySelector('#discipline-name').value = '';

        // Get the row index nr based on the row position in the table.
        const rows = module.querySelectorAll('[data-region="rows"] [data-row]');
        const rowArray = Array.from(rows);
        const index = rowArray.indexOf(row);
        // Set the title of the form to show the row number.
        form.querySelector('[data-region="rownumber"]').textContent =
            await getString('row', 'customfield_sprogramme', index + 1);
        form.dataset.action = region;

        // Attache the form to the first row for the first 8 rows.
        // Then attach it to 8 rows before the clicked row.
        // This makes sure the form is always visible.
        const setindex = index - 8;
        let attachTo;
        let attachToButton;
        if (setindex > 0) {
            attachTo = rowArray[setindex].querySelector(`[${region}]`);
            attachToButton = rowArray[setindex].querySelector(`[data-action="${btnAction}"]`);
        } else {
            attachTo = rowArray[0].querySelector(`[${region}]`);
            attachToButton = rowArray[0].querySelector(`[data-action="${btnAction}"]`);
        }
        attachTo.appendChild(form);

        // Position the form arrow next to the button that was clicked.
        const rectBtn = btn.getBoundingClientRect();
        const rectAttachToButton = attachToButton.getBoundingClientRect();
        arrow.style.top = rectBtn.top - rectAttachToButton.top + 'px';
        this.renderFormDisciplines(row.dataset.index, region);
        this.disableFormInput(form);
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

    /**
     * Get the selected discipline.
     * @param {object} form The form.
     * @return {Promise} The promise.
     */
    async getSelectedDiscipline(form) {
        const action = form.dataset.action;
        const disciplineid = form.querySelector('#discipline-id').value;
        const disciplinevalue = form.querySelector('#discipline-value').value;
        const disciplinename = form.querySelector('#discipline-name').value;

        if (!disciplineid || !disciplinevalue || !disciplinename) {
            form.querySelector('[data-region="warnings"]').innerHTML = 'Invalid input';
            return false;
        }
        const availableDisciplines = form.querySelectorAll(`[data-list="${action}"] [data-action="selectdiscipline"]`);
        // Find the discipline in the available disciplines based on the discipline id.
        const listedDiscipline = Array.from(availableDisciplines).find(d => d.dataset.id == disciplineid);
        if (!listedDiscipline || listedDiscipline.textContent !== disciplinename) {
            form.querySelector('[data-region="warnings"]').innerHTML =
                await getString('invalidinput', 'customfield_sprogramme');
            return false;
        }
        const displine = {
            id: disciplineid,
            name: disciplinename,
            percentage: parseInt(disciplinevalue),
        };
        // Return a promise.
        return new Promise((resolve) => {
            resolve(displine);
        });
    }

    /**
     * Load the discipline in the form.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    loadDiscipline(btn) {
        const form = document.querySelector('[data-region="disciplineform"]');
        form.querySelector('#discipline-id').value = btn.dataset.id;
        form.querySelector('#discipline-name').value = btn.dataset.name;
        form.querySelector('#discipline-value').value = btn.dataset.percentage;
        form.querySelector('#discipline-value').focus();
    }

    /**
     * Show the changes.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async showchanges(btn) {
        // Remove the active class from all buttons.
        const tds = document.querySelectorAll('[data-action="showchanges"]');
        tds.forEach(td => {
            if (td !== btn) {
                td.classList.remove('active');
            }
        });
        // Add the active class to the clicked button.
        btn.classList.add('active');
    }

    /**
     * Accept the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async acceptRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.acceptRfc({courseid: this.courseid, userid: userid});
        if (response) {
            this.getTableData();
        }
    }

    /**
     * Reject the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async rejectRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.rejectRfc({courseid: this.courseid, userid: userid});
        if (response) {
            this.getTableData();
        }
    }

    /**
     * Submit the RFC for approval.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async submitRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.submitRfc({courseid: this.courseid, userid: userid});
        if (response) {
            this.getTableData();
        }
    }

    /**
     * Cancel the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async cancelRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.cancelRfc({courseid: this.courseid, userid: userid});
        if (response) {
            this.getTableData();
        }
    }

    /**
     * Remove the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async removeRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.removeRfc({courseid: this.courseid, userid: userid});
        if (response) {
            this.getTableData();
        }
    }

    /**
     * Augment the table.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async augmentTable(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;

        const form = document.querySelector('[data-region="app"]');
        const resetRfc = form.querySelector('[data-action="resetrfc"]');
        this.resetRfc(resetRfc);
        resetRfc.classList.remove('d-none');
        const changeCells = form.querySelectorAll('[data-action="showchanges"]');
        changeCells.forEach(cell => {
            const input = cell.querySelector('[data-input="auto"]');
            if (input) {
                input.dataset.input = 'rfc';
                const changesdiv = cell.querySelector('[data-changes][data-userid="' + userid + '"]');
                if (!changesdiv) {
                    return;
                }
                const newvalue = changesdiv.dataset.newvalue;
                input.dataset.oldvalue = input.value;
                input.value = newvalue;
                input.dataset.rfcstate = '1';
                cell.classList.add('rfc');
            }
        });
        this.sumtotals();
    }

    /**
     * Reset the table to the original state.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async resetRfc(btn) {
        const form = document.querySelector('[data-region="app"]');
        const changeCells = form.querySelectorAll('[data-action="showchanges"]');
        changeCells.forEach(cell => {
            const input = cell.querySelector('[data-input="rfc"]');
            if (input) {
                input.dataset.input = 'auto';
                input.value = input.dataset.oldvalue;
                input.dataset.rfcstate = '0';
                cell.classList.remove('rfc');
            }
        });
        this.sumtotals();
        btn.classList.add('d-none');
    }

    /**
     * Disable the form input if the maximum number of disciplines is reached.
     * @param {object} form The form.
     * @return {void}
     */
    async disableFormInput(form) {
        const action = form.dataset.action;
        const rowid = form.querySelector('#rowid').value;
        const row = this.getRow(rowid);
        if ((action === 'data-disciplines' && row.disciplines.length >= 3) ||
            (action === 'data-competencies' && row.competencies.length >= 300)) {
            form.querySelector('input[type="search"]').disabled = true;
            form.querySelector('input[type="number"]').disabled = true;
            form.querySelector('button[data-action="discipline-confirm"]').disabled = true;
            form.querySelector('[data-region="warnings"]').innerHTML =
                await getString('maxdisciplines', 'customfield_sprogramme', 3);
        } else {
            form.querySelector('input[type="search"]').disabled = false;
            form.querySelector('input[type="number"]').disabled = false;
            form.querySelector('button[data-action="discipline-confirm"]').disabled = false;
            form.querySelector('[data-region="warnings"]').innerHTML = '';
        }
    }

    // Add a discipline to the row.
    async addDiscipline() {
        const form = document.querySelector('[data-region="disciplineform"]');
        const action = form.dataset.action;
        const discipline = await this.getSelectedDiscipline(form);
        if (!discipline) {
            return;
        }
        const rowid = form.querySelector('#rowid').value;
        const row = this.getRow(rowid);
        // Update or add the discipline to the row.
        const containername = action === 'data-disciplines' ? 'container-disciplines' : 'container-competencies';
        let disciplineIndex = 0;
        let maxpercentage = 100;
        if (action === 'data-disciplines') {
            disciplineIndex = row.disciplines.findIndex(d => d.id == discipline.id);
            maxpercentage = 100 - row.disciplines.reduce((acc, comp) => acc + parseInt(comp.percentage), 0);
        }
        if (action === 'data-competencies') {
            disciplineIndex = row.competencies.findIndex(d => d.id == discipline.id);
            maxpercentage = 100 - row.competencies.reduce((acc, disc) => acc + parseInt(disc.percentage), 0);
        }
        if (discipline.percentage > maxpercentage) {
            form.querySelector('[data-region="warnings"]').innerHTML =
                await getString('maxpercentage', 'customfield_sprogramme', maxpercentage);
            return;
        } else {
            form.querySelector('[data-region="warnings"]').innerHTML = '';
        }
        const container = document.querySelector(
            `[${action}][data-rowid="${rowid}"] [data-region="${containername}"]`);
        const selectedcontainer = form.querySelector('[data-region="selected-disciplines"]');
        if (disciplineIndex > -1) {
            if (action === 'data-disciplines') {
                row.disciplines[disciplineIndex] = discipline;
            }
            if (action === 'data-competencies') {
                row.competencies[disciplineIndex] = discipline;
            }
            const rendered = container.querySelector('[data-id="' + discipline.id + '"]');
            const selected = selectedcontainer.querySelector('[data-id="' + discipline.id + '"]');
            const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/discipline', discipline);
            await Templates.replaceNode(rendered, html, js);
            await Templates.replaceNode(selected, html, js);

        } else {
            if (action === 'data-disciplines') {
                row.disciplines.push(discipline);
            }
            if (action === 'data-competencies') {
                row.competencies.push(discipline);
            }
            const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/discipline', discipline);
            await Templates.appendNodeContents(container, html, js);
            await Templates.appendNodeContents(selectedcontainer, html, js);
        }
        this.disableFormInput(form);
    }

    /**
     * Render the disciplines in the form.
     * @param {int} rowid The rowid.
     * @param {String} region The region.
     * @return {void}
     */
    async renderFormDisciplines(rowid, region) {
        const row = this.getRow(rowid);
        let disciplines = [];
        if (region === 'data-disciplines') {
            disciplines = row.disciplines;
        }
        if (region === 'data-competencies') {
            disciplines = row.competencies;
        }
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
        const action = form.dataset.action;
        const containername = action === 'data-disciplines' ? 'container-disciplines' : 'container-competencies';
        const rowid = form.querySelector('#rowid').value;
        const disciplineid = btn.closest('[data-id]').dataset.id;

        // Remove the discipline from the row.
        if (action === 'data-disciplines') {
            const row = this.getRow(rowid);
            const index = row.disciplines.findIndex(d => d.id == disciplineid);
            row.disciplines.splice(index, 1);
        }
        if (action === 'data-competencies') {
            const row = this.getRow(rowid);
            const index = row.competencies.findIndex(d => d.id == disciplineid);
            row.competencies.splice(index, 1);
        }

        // Remove the discipline/competency from the view.
        const container = document.querySelector(
            `[${action}][data-rowid="${rowid}"] [data-region="${containername}"]`);
        const selectedcontainer = document.querySelector('[data-region="selected-disciplines"]');
        const discipline = container.querySelector('[data-id="' + disciplineid + '"]');
        const selected = selectedcontainer.querySelector('[data-id="' + disciplineid + '"]');
        container.removeChild(discipline);
        selectedcontainer.removeChild(selected);
        this.disableFormInput(form);
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
                    const nextInput = allRows[i + 1].querySelector(`[data-columnid="${currentColumn}"] input`);
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
                if (e.key === 'ArrowUp' && i > 0) {
                    const previousInput = allRows[i - 1].querySelector(`[data-columnid="${currentColumn}"] input`);
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
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 */
const init = (element, courseid) => {
    new Manager(element, courseid);
};

export default {
    init: init,
};