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
        this.courseid = parseInt(courseid);
        this.addEventListeners();
        this.getDatagrid();
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
            }
        });
        // Listen to all changes in the table.
        form.addEventListener('change', (e) => {
            const input = e.target.closest('[data-input]');
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

        form.addEventListener('dragstart', (e) => {
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
            window.console.log('RowId: ' + rowId + ' PrevRowId: ' + prevRowId + ' ModuleId: ' + moduleId);
            Repository.updateSortOrder(
                {
                    type: 'row',
                    courseid: this.courseid,
                    moduleid: moduleId,
                    id: rowId,
                    previd: prevRowId
                }
            );
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
    }

    async getDatagrid() {
        await this.getTableData();
        await this.getTableConfig();
    }

    /**
     * Get the table configuration.
     * @return {Promise} The promise.
     */
    async getTableConfig() {
        const response = await Repository.getColumns({table: this.table});
        await State.setValue('columns', response.columns);
    }

    /**
     * Get the table data.
     * @return {void}
     */
    async getTableData() {
        try {
            const response = await Repository.getData({courseid: this.courseid});
            // Validate the response, the response.date should be a string that can be parsed to a JSON object.
            if (response.modules.length > 0) {
                const modules = this.parseModules(response.modules);
                State.setValue('modules', modules);
            } else {
                const moduleid = await this.createModule('Module 1', 0);
                await this.createRow(moduleid, 0, 0);
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
     * Clean the Modules array.
     * @param {Array} modules The modules.
     * @return {Array} The cleaned modules.
     */
    cleanModules(modules) {
        const cleanedModules = [];
        modules.forEach(module => {
            const rows = module.rows;
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
            const cleanedModule = {};
            cleanedModule.id = module.moduleid;
            cleanedModule.sortorder = module.modulesortorder;
            cleanedModule.name = module.modulename;
            cleanedModule.rows = cleanedRows;
            cleanedModules.push(cleanedModule);
        });
        return cleanedModules;
    }

    /**
     * Set the table data.
     * @return {void}
     */
    async setTableData() {
        const set = debounce(async() => {
            const modules = State.getValue('modules');
            const cleanedModules = this.cleanModules(modules);
            const response = await Repository.setData({courseid: this.courseid, modules: cleanedModules});
            if (!response) {
                Notification.exception('No response from the server');
            }
        }, 600);
        set();
    }

    /**
     * Actions.
     * @param {object} btn The button that was clicked.
     */
    actions(btn) {
        if (btn.dataset.action === 'addrow') {
            this.addRow(btn);
        }
        if (btn.dataset.action === 'deleterow') {
            this.deleteRow(btn);
        }
        if (btn.dataset.action === 'addmodule') {
            this.addModule(btn);
        }
        if (btn.dataset.action === 'deletemodule') {
            this.deleteModule(btn);
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
     * Inject a new row after this row.
     * @param {object} btn The button that was clicked.
     */
    async addRow(btn) {
        const modules = State.getValue('modules');

        const btnRow = btn.closest('[data-row]');
        const rowid = btnRow.dataset.index;
        const moduleid = btn.closest('[data-region="module"]').dataset.id;
        const module = modules.find(m => m.moduleid == moduleid);
        const rows = module.rows;

        const row = await this.createRow(moduleid, rowid);
        if (!row) {
            return;
        }
        // Inject the row after the clicked row.
        rows.splice(rows.indexOf(rows.find(r => r.id == rowid)) + 1, 0, row);
        // this.resetRowSortorder();
        State.setValue('modules', modules);
    }

    /**
     * Create a new row.
     *
     * @param {Number} moduleid The moduleid.
     * @param {Number} prevrowid The previous rowid.
     * @return {Promise} The promise.
     */
    async createRow(moduleid, prevrowid) {
        const rowid = await Repository.createRow({courseid: this.courseid, moduleid: moduleid, prevrowid: prevrowid});
        return new Promise((resolve) => {
            const row = {};
            row.id = rowid;
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
            row.disciplines = [];
            resolve(row);
        });
    }

    /**
     * Delete a row.
     * @param {Object} btn The button that was clicked.
     * @return {Promise} The promise.
     */
    async deleteRow(btn) {
        const modules = State.getValue('modules');
        const rowid = btn.closest('[data-row]').dataset.index;
        const moduleid = btn.closest('[data-region="module"]').dataset.id;
        const module = modules.find(m => m.moduleid == moduleid);
        const response = await Repository.deleteRow({courseid: this.courseid, rowid: rowid});
        return new Promise((resolve) => {
            if (response) {
                const rows = module.rows;
                const index = Array.from(btn.closest('[data-region="rows"]').children).indexOf(btn.closest('[data-row]'));
                rows.splice(index, 1);
                this.resetRowSortorder();
                State.setValue('modules', modules);
            }
            resolve(rowid);
        });
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
        const modules = State.getValue('modules');
        modules.forEach(module => {
            // Find the correct cell in the row.
            const rowIndex = module.rows.findIndex(r => r.id == index);
            if (rowIndex === -1) {
                return;
            }
            const cellIndex = module.rows[rowIndex].cells.findIndex(c => c.columnid == columnid);
            module.rows[rowIndex].cells[cellIndex].value = value;
        });
        this.setTableData();
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
        this.setTableData();
    }

    /**
     * Delete a module.
     * @param {object} btn The button that was clicked.
     * @return {Promise} The promise.
     * @return {void}
     */
    async deleteModule(btn) {
        const modules = State.getValue('modules');
        const moduleid = btn.closest('[data-region="module"]').dataset.id;
        const module = modules.find(m => m.moduleid == moduleid);
        const response = await Repository.deleteModule({courseid: this.courseid, moduleid: moduleid});
        return new Promise((resolve) => {
            if (response) {
                const index = modules.indexOf(module);
                modules.splice(index, 1);
                State.setValue('modules', modules);
            }
            resolve(moduleid);
        });
    }

    /**
     * Create a new module.
     * @param {String} name The name.
     * @param {Number} index The index.
     * @return {Promise} The promise.
     */
    async createModule(name, index) {
        const id = await Repository.createModule({name: name, courseid: this.courseid, sortorder: index});
        return new Promise((resolve) => {
            resolve(id);
        });
    }

    /**
     * Add a new module.
     * @param {object} btn The button that was clicked.
     * @return {void}
     */
    async addModule(btn) {
        const modules = State.getValue('modules');
        const moduleRow = btn.closest('[data-region="module"]');
        const index = Array.from(moduleRow.parentElement.children).indexOf(moduleRow);
        const moduleid = await this.createModule('Module ' + (index + 2), index);
        const row = await this.createRow(moduleid, 0);
        const module = {
            moduleid: moduleid,
            modulesortorder: index + 1,
            modulename: 'Module ' + (index + 1),
            rows: [row],
        };
        modules.push(module);
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
     * Reset the row sortorder values.
     * @return {void}
     */
    resetRowSortorder() {
        const modules = State.getValue('modules');
        modules.forEach(module => {
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
        const row = btn.closest('[data-row]');
        const module = btn.closest('[data-region="module"]');
        const form = document.querySelector('[data-region="disciplineform"]');
        form.querySelector('#rowid').value = row.dataset.index;
        const arrow = form.querySelector('.formarrow');


        // Get the row index nr based on the row position in the table.
        const rows = module.querySelectorAll('[data-region="rows"] [data-row]');
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
        const row = this.getRow(rowid);
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
        const row = this.getRow(rowid);
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
        const row = this.getRow(rowid);
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