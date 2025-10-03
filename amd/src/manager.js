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
import {getStrings} from 'core/str';
import {debounce} from 'core/utils';
import componentInit from './local/components/table';
import Pending from 'core/pending'; // For Behat to make sure that async calls are finished.
import './tagmanager';
import initProgrammeForm from './programme_form';

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
     * The datafieldid.
     * @type {Number}
     */
    datafieldid;

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
     * @param {String} datafieldid The datafieldid.
     * @return {void}
     */
    constructor(element, datafieldid) {
        this.element = element;
        this.datafieldid = parseInt(datafieldid);
        this.addEventListeners();
        this.getTableData();
    }

    /**
     * Add event listeners.
     * @return {void}
     */
    addEventListeners() {
        document.addEventListener('click', (e) => {
            let btn = e.target.closest('.modal-customfield_sprogramme_editor [data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn.dataset.action, btn);
            }

        });
        // Listen to all changes in the table.
        const form = document.querySelector('[data-region="app"]');
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
        // Resize the textareas when needed.
        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'TEXTAREA') {
                const textarea = e.target;
                // Resize the textarea to fit the content.
                textarea.style.height = 'auto'; // Reset height to auto to shrink if needed.
                textarea.style.height = `${textarea.scrollHeight + 1}px`; // Set height to scrollHeight to fit content.
                textarea.dataset.height = textarea.scrollHeight + 1; // Store the height in a data attribute.
            }
        });
        // Listen to the arrow down and up keys to navigate to the next or previous row.
        form.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                this.navigate(e);
                e.preventDefault();
            }
        });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
        });

        let dragging = null;

        form.addEventListener('dragstart', (e) => {
            const handle = e.target.closest('[data-region="dragicon"]');
            if (!handle) {
                e.preventDefault();
                return;
            }
            dragging = handle.closest('tr');
            e.dataTransfer.effectAllowed = 'move';
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
    }

    /**
     * Get the table data.
     * @return {void}
     */
    async getTableData() {
        try {
            const response = await Repository.getData({datafieldid: this.datafieldid, showrfc: 1});
            if (response.modules.length > 0) {
                const modules = this.parseModules(response);
                const columns = response.columns;

                State.setValue('columns', [...columns]);
                State.setValue('modules', modules);
                State.setValue('rfc', response.rfc ?? []);
                State.setValue('editbuttons', {datafieldid: this.datafieldid, canedit: response.canedit});
                this.sumtotals();
            } else {
                const response = await Repository.getColumns({datafieldid: this.datafieldid});
                const columns = response.columns;
                State.setValue('columns', [...columns]);
                State.setValue('modules', []);
                State.setValue('rfc', []);
                State.setValue('editbuttons', {datafieldid: this.datafieldid, canedit: response.canedit});
                this.addModule();
            }
        } catch (error) {
            Notification.exception(error);
        }
    }

    /**
     * Parse the response, add the correct column properties to each cell.
     * @param {Array} response The response.
     * @return {Array} The parsed rows.
     */
    parseModules(response) {
        response.modules.forEach(mod => {
            mod.editor = response.canedit;
            mod.rows.map(row => {
                row.cells = row.cells.map(cell => {
                    const column = response.columns.find(column => column.column == cell.column);
                    // Clone the column properties to the cell but keep the cell properties.
                    cell = Object.assign({}, cell, column);
                    if (cell.type === 'select') {
                        // Clone the options array to avoid shared references.
                        cell.options = cell.options.map(option => {
                            const clonedOption = Object.assign({}, option);
                            if (clonedOption.name == cell.value) {
                                clonedOption.selected = true;
                            }
                            return clonedOption;
                        });
                    }
                    cell.changed = cell.value !== cell.oldvalue;
                    return cell;
                });
                return row;
            });
        });
        return response.modules;
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
                'todelete': 'false',
                'cells': {
                    'type': 'type',
                    'column': 'column',
                    'value': 'value',
                    'group': 'group',
                    'oldvalue': 'oldvalue',
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
     * Validate the modules.
     * @return {boolean} True if the modules are valid.
     */
    validateModules() {
        const modules = State.getValue('modules');
        let result = true;
        if (modules.length === 0) {
            result = false;
            return result;
        }
        modules.forEach(module => {
            if (!module.modulename || module.modulename.trim() === '') {
                module.error = true;
            }
            module.rows.forEach(row => {
                if (row.deleted) {
                    return; // Skip deleted rows.
                }
                if (row.cells.length === 0) {
                    row.error = true;
                    result = false;
                } else {
                    if (!this.checkRow(row)) {
                        result = false;
                    }
                }
            });
        });
        State.setValue('modules', modules);
        return result;
    }

    /**
     * Check the row, if there are grouped cells, only one can have a value. and one should have a value.
     * @param {Object} row The row to check.
     * @return {boolean} True if the row is valid.
     */
    checkRow(row) {
        const groups = {};
        let result = true;
        row.cells.forEach(cell => {
            if (cell.group) {
                if (!groups[cell.group]) {
                    groups[cell.group] = [];
                }
                if (cell.value && cell.value > 0) {
                    groups[cell.group].push(cell);
                }
            }
        });
        Object.keys(groups).forEach(group => {
            if (groups[group].length > 1) {
                // More than one cell in the group has a value.
                groups[group].forEach(cell => {
                    cell.error = true;
                });
                row.error = true;
                result = false;
            } else if (groups[group].length === 0) {
                // No cell in the group has a value.
                row.error = true;
                result = false;
            } else {
                row.error = false;
                row.error = false;
            }
        });
        return result;
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
                moduleid: module.moduleid,
                modulename: module.modulename,
                modulesortorder: module.modulesortorder,
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
        const pending = new Pending('customfield_sprogramme/manager:setTableData');
        const set = debounce(async() => {
            const saveConfirmButton = document.querySelector('[data-action="saveconfirm"]');
            saveConfirmButton.classList.add('saving');
            if (!this.validateModules()) {
                pending.resolve();
                return '';
            }
            const modules = State.getValue('modules');
            const cleanedModules = this.cleanModules(modules);
            const response = await Repository.setData({datafieldid: this.datafieldid, modules: cleanedModules});
            if (!response) {
                Notification.exception('No response from the server');
            } else {
                await this.getTableData();
                const update = await Repository.getData({datafieldid: this.datafieldid, showrfc: 0});
                const modulesStatic = this.parseModules(update);
                State.setValue('modulesstatic', modulesStatic);
            }
            pending.resolve();
            setTimeout(() => {
                saveConfirmButton.classList.remove('saving');
            }, 200);
        }, 600);
        set();
    }

    /**
     * Actions.
     * @param {string} action The button that was clicked.
     * @param {HTMLElement|null} element The element that was clicked.
     */
    actions(action, element) {
        const actionMap = {
            'addrow': this.addRow,
            'deleterow': this.deleteRow,
            'addmodule': this.addModule,
            'deletemodule': this.deleteModule,
            'saveconfirm': this.setTableData,
            'showchanges': this.showchanges,
            'closechanges': this.closeChanges,
            'acceptrfc': this.acceptRfc,
            'rejectrfc': this.rejectRfc,
            'submitrfc': this.submitRfc,
            'cancelrfc': this.cancelRfc,
            'removerfc': this.removeRfc,
            'resetrfc': this.resetRfc,
            'closeform': this.closeForm,
            'hide': this.closeForm,
            'downloadcsv': this.downloadCsv,
            'augmenttable': this.augmentTable,
        };
        if (actionMap[action]) {
            actionMap[action].call(this, element);
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
        this.rowNumber = this.rowNumber - 1;
        row.id = this.rowNumber;
        const columns = State.getValue('columns');
        // The copy the columns to the row and call them cells.
        row.cells = columns.map(column => structuredClone(column));
        // Set the correct types for the cells.
        row.cells.forEach(cell => {
            cell.isnewcell = true;
            cell.value = null;
            cell[cell.type] = true;
            cell.oldvalue = null;
            cell.changed = false;
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
                if (rowid > 0) {
                    modulefound.rows[rowIndex].deleted = true;
                    // Set the changed attribute to each cell in the row.
                    modulefound.rows[rowIndex].cells.forEach(cell => {
                        if (cell.value !== null && (cell.type == 'float' || cell.type == 'number')) {
                            cell.changed = true;
                            cell.value = null; // Clear the value.
                        }
                    });
                } else {
                    // Remove the row from the module.
                    modulefound.rows.splice(rowIndex, 1);
                }
                State.setValue('modules', modules);
            } else {
                Notification.exception('Row not found');
            }
        }
        this.sumtotals();
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
            const cell = module.rows[rowIndex].cells[cellIndex];
            cell.value = value ? value : null;
            if (input.dataset.height) {
                cell.height = input.dataset.height;
            }
            // Find the other cells with the same group and null the value.
            if (group) {
                module.rows[rowIndex].cells.forEach(c => {
                    if (c.group === group && c.columnid !== columnid && c.value !== null) {
                        c.value = null;
                    }
                });
            }
            if (cell.type == 'select') {
                // Find the option that matches the value and set it as selected.
                cell.options.forEach(option => {
                    option.selected = (option.name === value);
                });
            }
        });
        this.markchanges();
        this.sumtotals();
        State.setValue('modules', modules);
    }

    /**
     * Markchanges.
     * Mark the cells that have changed.
     */
    markchanges() {
        const modules = State.getValue('modules');
        modules.forEach(module => {
            module.rows.forEach(row => {
                row.cells.forEach(cell => {
                    cell.changed = cell.value != cell.oldvalue;
                });
            });
        });
        State.setValue('modules', modules);
    }

    /**
     * Sumtotals.
     * Sum all columns.
     */
    sumtotals() {
        const columnsData = State.getValue('columns');
        // Reset the sum for all columns.
        columnsData.forEach(column => {
            column.sum = 0;
            column.newsum = 0;
            column.hasnewsum = false;
            column.changed = false;
        });
        const modules = State.getValue('modules');
        let overaltotals = 0;
        let newsumtotals = 0;
        modules.forEach(module => {
            module.rows.forEach(row => {
                row.cells.forEach(cell => {
                    if (cell.type === 'number' || cell.type === 'float') {
                        const column = columnsData.find(c => c.columnid === cell.columnid);
                        if (column) {
                            if (cell.changed) {
                                column.sum = (parseFloat(column.sum) || 0) + parseFloat(cell.oldvalue);
                            } else if (cell.value && cell.value !== null) {
                                column.sum = (parseFloat(column.sum) || 0) + parseFloat(cell.value);
                            }
                            if (cell.value) {
                                column.newsum = (parseFloat(column.newsum) || 0) + parseFloat(cell.value);
                                column.hasnewsum = true;
                            }
                            if (column.sum == 0 && column.newsum > 0) {
                                column.hasnewsum = true;
                                column.sum = " 0"; // If the sum is 0 and the new sum is greater than 0, set the sum to 0.
                            }
                        }
                        if (cell.changed) {
                            column.changed = true;
                        }
                    }
                });
            });
        });
        let totalsChanged = false;
        columnsData.forEach(column => {
            if (column.type === 'number' || column.type === 'float') {
                totalsChanged = totalsChanged || column.changed;
                overaltotals += parseFloat(column.sum) || 0;
                newsumtotals += parseFloat(column.newsum) || 0;
            }
        });
        columnsData[0].overaltotals = overaltotals;
        columnsData[0].newsumtotals = newsumtotals;
        columnsData[0].totalschanged = totalsChanged;
        State.setValue('columns', columnsData);
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
            modules[moduleIndex].rows.forEach(row => {
                row.deleted = true; // Mark all rows as deleted.
                row.cells.forEach(cell => {
                    if (cell.value !== null && (cell.type == 'float' || cell.type == 'number')) {
                        cell.changed = true;
                        cell.value = null; // Clear the value.
                    }
                });
            });
            State.setValue('modules', modules);
        }
    }

    /**
     * Create a new module.
     * @return {Integer} The module id.
     */
    createModule() {
        this.moduleNumber = this.moduleNumber - 1;
        return this.moduleNumber;
    }

    /**
     * Add a new module.
     * @return {void}
     */
    addModule() {
        const modules = State.getValue('modules');
        const moduleid = this.createModule();
        const row = this.createRow();
        const module = {
            moduleid: moduleid,
            modulename: ' ',
            deleted: false,
            editor: true,
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
     * Close the changes.
     * @return {void}
     */
    async closeChanges() {
        // Remove the active class from all buttons.
        const tds = document.querySelectorAll('[data-action="showchanges"]');
        tds.forEach(td => {
            td.classList.remove('active');
        });
    }

    /**
     * Accept the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async acceptRfc(btn) {
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.acceptRfc({datafieldid: this.datafieldid, userid: userid});
        if (response) {
            await this.getTableData();
            const update = await Repository.getData({datafieldid: this.datafieldid, showrfc: 0});
            const modulesStatic = this.parseModules(update);
            State.setValue('modulesstatic', modulesStatic);
        }
    }

    /**
     * Reject the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async rejectRfc(btn) {
        const pending = new Pending('customfield_sprogramme/manager:rejectRFC');
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.rejectRfc({datafieldid: this.datafieldid, userid: userid});
        if (response) {
            await this.getTableData();
        }
        pending.resolve();
    }

    /**
     * Submit the RFC for approval.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async submitRfc(btn) {
        const pending = new Pending('customfield_sprogramme/manager:submitRFC');
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.submitRfc({datafieldid: this.datafieldid, userid: userid});
        if (response) {
            await this.getTableData();
        }
        pending.resolve();
    }

    /**
     * Cancel the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async cancelRfc(btn) {
        const pending = new Pending('customfield_sprogramme/manager:cancelRFC');
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.cancelRfc({datafieldid: this.datafieldid, userid: userid});
        if (response) {
            await this.getTableData();
        }
        pending.resolve();
    }

    /**
     * Remove the RFC.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async removeRfc(btn) {
        const pending = new Pending('customfield_sprogramme/manager:removeRFC');
        const userid = btn.closest('[data-rfc]').dataset.userid;
        const response = await Repository.removeRfc({datafieldid: this.datafieldid, userid: userid});
        if (response) {
            await this.getTableData();
        }
        pending.resolve();
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
     * Augment the table with additional data.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async augmentTable(btn) {
        const modules = State.getValue('modules');
        modules.forEach(module => {
            module.rows.forEach(row => {
                row.cells.forEach(cell => {
                    if (cell.oldvalue != cell.value) {
                        cell.changes = {
                            oldvalue: cell.oldvalue ? cell.oldvalue : '0',
                            newvalue: cell.value,
                        };
                        cell.changed = true;
                    } else {
                        cell.changes = null;
                        cell.changed = false;
                    }
                });
            });
        });
        State.setValue('modules', modules);
        // Add the augment class to the button.
        btn.classList.add('active');
    }

    /**
     * Send a closeform custom event.
     * @return {void}
     */
    async closeForm() {
        // Check if there are unsaved changes.
        const modules = State.getValue('modules');
        const rfc = State.getValue('rfc');
        const event = new CustomEvent('closeform', {
            bubbles: true,
            composed: true,
        });
        if (rfc && rfc.issubmitted) {
            document.dispatchEvent(event);
            return;
        }

        const confirmationStrings = await getStrings([
            {
                key: 'confirm',
                component: 'customfield_sprogramme',
            },
            {
                key: 'unsavedchanges',
                component: 'customfield_sprogramme',
            },
            {
                key: 'closewithoutsaving',
                component: 'customfield_sprogramme',
            },
            {
                key: 'cancel',
                component: 'customfield_sprogramme',
            },
        ]);

        const hasChanges = modules.some(module => module.rows.some(
            row => row.cells.some(cell => cell.changed || (cell.isnewcell ?? false))
        ));
        if (hasChanges) {
            Notification.confirm(
                ...confirmationStrings,
                () => {
                    document.dispatchEvent(event);
                },
                () => {
                    // Do nothing, the user cancelled the action.
                },
            );
            return;
        } else {
            document.dispatchEvent(event);
        }
    }

    /**
     * Download the table as a CSV file.
     * @return {void}
     */
    async downloadCsv() {
        const csv = await Repository.csvData({datafieldid: this.datafieldid});
        const blob = new Blob([csv.csv], {type: 'text/csv'});
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = csv.filename;
        a.click();
        window.URL.revokeObjectURL(url);
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
 * @param {String} datafieldid The datafieldid
 * @return {Manager} The manager instance.
 */
const init = (element, datafieldid) => {
    componentInit();
    const manager = new Manager(element, datafieldid);
    initProgrammeForm(manager);
    return manager;
};

export default {
    init: init,
};
