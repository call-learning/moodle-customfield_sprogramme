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
 * TODO describe module tagmanager
 *
 * @module     customfield_sprogramme/tagmanager
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import State from 'customfield_sprogramme/local/state';
import Repository from 'customfield_sprogramme/local/repository';
import Templates from 'core/templates';
import {getString} from 'core/str';

class TagManager {

    modules = [];

    /**
     * Constructor.
     */
    constructor() {
        this.addEventListeners();
        this.fetchTags();
    }

    /**
     * Set the modules.
     * @param {Array} modules The modules.
     */
    async setModules(modules) {
        this.modules = modules;
    }

    /**
     * Fetch the tags.
     */
    async fetchTags() {
        const tags = await Repository.getTags();
        State.setValue('tags', tags);
    }

    /**
     * Add event listeners.
     */
    addEventListeners() {
        document.addEventListener('click', (e) => {
            let btn = e.target.closest('[data-region="app"] [data-action]');
            if (btn) {
                e.preventDefault();
                this.actions(btn);
            }
        });
    }

    /**
     * Handle actions.
     * @param {HTMLElement} btn The button.
     */
    actions(btn) {
        const actionMap = {
            'adddisc': this.showDisciplineForm,
            'addcomp': this.showCompetenciesForm,
            'removetag': this.removeTag,
            'closetagform': this.closeTagForm,
            'selecttag': this.selectTag,
            'tag-confirm': this.addTag,
            'loadtag': this.loadTag,
        };
        const action = btn.dataset.action;
        if (actionMap[action]) {
            actionMap[action].call(this, btn);
        }
    }

    /**
     * Get the position of a row in a module
     * if the row is within the first 8 rows, return the rowid of the first row.
     * if the row is the 8th row or higher, return the rowid of the row minus 8.
     * @param {String} rowId The row id.
     * @returns {Int} The rowId of the target row.
     */
    getTargetRow(rowId) {
        const modules = State.getValue('modules');
        if (!modules || modules.length === 0) {
            return 0;
        }

        for (const module of modules) {
            const rowFound = module.rows.find(row => row.id === parseInt(rowId));
            if (rowFound) {
                const position = module.rows.indexOf(rowFound);
                if (position < 6) {
                    const row = module.rows[0];
                    return row ? row.id : 0;
                } else {
                    return module.rows[position - 6].id;
                }
            }
        }
        return 0; // Row not found in any module.
    }

    /**
     * Get the number of pixels to shift the arrow downwards.
     * @param {String} rowId The row id.
     * @param {Int} position The position of the row the form is attached to.
     * @param {String} type The type of tag (disciplines or competencies).
     * @returns {Int} The number of pixels to shift the arrow downwards.
     */
    getRowOffset(rowId, position, type) {
        if (parseInt(rowId) === position || position === 0) {
            return 0; // No offset needed if the row is the target row.
        }
        const target = document.querySelector(`[data-${type}][data-rowid="${position}"]`);
        const row = document.querySelector(`[data-${type}][data-rowid="${rowId}"]`);
        if (!target || !row) {
            return 0; // If the target or row is not found, no offset needed.
        }
        const targetRect = target.getBoundingClientRect();
        const rowRect = row.getBoundingClientRect();
        // Calculate the offset based on the target and row positions.
        const offset = rowRect.top - targetRect.top;
        return offset;
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
     * Get the tags from the current row.
     * @param {String} rowId The row id.
     * @param {String} type The type of tags to get.
     */
    getTagsFromRow(rowId, type) {
        const row = this.getRow(rowId);
        if (!row || !row[type]) {
            return [];
        }
        return row[type];
    }

    /**
     * Show the discipline form.
     * @param {HTMLElement} btn The button.
     */
    async showDisciplineForm(btn) {
        const rowId = btn.dataset.id;
        this.closeTagForm();
        this.renderForm(rowId, 'disciplines');
    }

    /**
     * Show the competencies form.
     * @param {HTMLElement} btn The button.
     */
    async showCompetenciesForm(btn) {
        const rowId = btn.dataset.id;
        this.closeTagForm();
        this.renderForm(rowId, 'competencies');
    }

    /**
     * Render the form
     * @param {Number} rowId The row id.
     * @param {String} type The type of tag to render.
     * @returns {Promise<String>} The rendered form.
     */
    async renderForm(rowId, type) {
        const setTags = this.getTagsFromRow(rowId, type);
        const position = this.getTargetRow(rowId);
        const arrowOffset = this.getRowOffset(rowId, position, type);
        const tags = State.getValue('tags');
        const target = document.querySelector(`[data-${type}][data-rowid="${position}"]`);
        const maxTagsReached = this.isMaxTagsReached(rowId, type);
        const {html, js} = await Templates.renderForPromise('customfield_sprogramme/tagform', {
            tagtype: type,
            taglist: tags[type],
            rowid: rowId,
            settags: setTags,
            hasmaxtags: maxTagsReached,
            arrowoffset: arrowOffset,
        });
        await Templates.appendNodeContents(target, html, js);
    }

    /**
     * Check if the maximum number of tags is reached for the given type.
     * @param {Number} rowId The row id.
     * @param {String} type The type of tag to render.
     * @returns {boolean} True if the maximum number of tags is reached, false otherwise
     */
    isMaxTagsReached(rowId, type) {
        const row = this.getRow(rowId);
        if (!row || !row[type]) {
            return false;
        }
        const config = {
            'disciplines': 3,
            'competencies': 100,
        };
        return row[type].length >= config[type];
    }


    /**
     * Destroy the discipline form.
     */
    closeTagForm() {
        const form = document.querySelector('[data-region="tagform"]');
        if (form) {
            form.remove();
        }
    }

    /**
     * Set the tag form with the given discipline.
     * @param {Object} tag The tag.
     */
    setTagForm(tag) {
        const form = document.querySelector('[data-region="tagform"]');
        if (!form) {
            return;
        }
        const input = form.querySelector('input[type="search"]');
        if (input) {
            input.value = tag.name;
        }
        const tagId = form.querySelector('#tag-id');
        if (tagId) {
            tagId.value = tag.id;
        }
        const select = form.querySelector('#tag-value');
        if (select) {
            select.focus();
        }
    }

    /**
     * Select a tag.
     * @param {HTMLElement} btn The button.
     */
    selectTag(btn) {
        const option = btn.closest('[data-option]');
        const tag = {
            id: option.dataset.id,
            name: option.textContent,
        };
        this.setTagForm(tag);
    }

    /**
     * Get a tag from the State tags.
     *
     * @param {String} tagId The tag id.
     * @param {String} type The type of tag to get.
     * traverse the tags in the state and return the tag with the given id.
     */
    getTag(tagId, type) {
        const tags = State.getValue('tags');
        if (!tags || !tags[type]) {
            return null;
        }
        // Construct a flat array of tags.
        const flatTags = tags[type].reduce((acc, tag) => {
            acc.push(tag);
            if (tag.items && tag.items.length > 0) {
                acc.push(...tag.items);
            }
            return acc;
        }, []);
        // Find the tag with the given id.
        return flatTags.find(tag => tag.uniqueid === parseInt(tagId) || tag.id === parseInt(tagId));
    }


    /**
     * Add a tag selected in the tagform.
     */
    async addTag() {
        const modules = State.getValue('modules');
        const form = document.querySelector('[data-region="tagform"]');
        const warnings = form.querySelector('[data-region="warnings"]');
        if (!form) {
            return;
        }

        const rowId = form.dataset.rowid;
        const type = form.dataset.type;
        const tagId = parseInt(form.querySelector('#tag-id').value);
        const tagValue = parseInt(form.querySelector('#tag-value').value);

        if (!tagId || !tagValue) {
            return;
        }

        // Find name of the tag.
        const selectedTag = this.getTag(tagId, type);
        if (!selectedTag) {
            return;
        }

        // Add the tag to the row.
        const row = this.getRow(rowId);
        if (!row) {
            return;
        }
        if (!row[type]) {
            row[type] = [];
        }

        // Validate the value, the maximum value is 100 and the total of all tags should not exceed 100.
        const maxPercentage = 100 - row[type].reduce((acc, tag) => acc + parseInt(tag.percentage), 0);
        if (parseInt(tagValue) > maxPercentage) {
            warnings.innerHTML = await getString('maxpercentage', 'customfield_sprogramme', maxPercentage);
            return;
        } else {
            warnings.innerHTML = '';
        }

        // Check if the tag already exists in the row.
        const existingTags = this.getTagsFromRow(rowId, type);
        if (!existingTags) {
            row[type] = [];
        }
        const existingTag = existingTags.find(tag => tag.id === tagId || tag.uniqueid === tagId);
        if (existingTag) {
            existingTag.percentage = tagValue;
        } else {
            const tag = {
                id: tagId,
                name: selectedTag ? selectedTag.name : 'Unknown',
                percentage: tagValue,
            };
            row[type].push(tag);
        }

        // Might need to avoid this. it hides the tag form.
        State.setValue('modules', modules);
    }

    /**
     * Remove a tag from the row.
     * @param {HTMLElement} btn The button.
     */
    async removeTag(btn) {
        const form = document.querySelector('[data-region="tagform"]');
        if (!form) {
            return;
        }
        const type = form.dataset.type;
        const rowId = parseInt(form.dataset.rowid);
        const tagId = parseInt(btn.dataset.id);

        const modules = State.getValue('modules');
        const row = this.getRow(rowId);
        if (!row || !row[type]) {
            return;
        }
        // Remove the tag from the row.
        row[type] = row[type].filter(tag => tag.id !== parseInt(tagId));
        // Update the state.
        State.setValue('modules', modules);
    }


    /**
     * Load a tag into the tag form.
     * @param {HTMLElement} btn The button.
     */
    async loadTag(btn) {
        const form = document.querySelector('[data-region="tagform"]');
        if (!form) {
            return;
        }
        form.querySelector('#tag-id').value = btn.dataset.id;
        form.querySelector('#tag-value').value = btn.dataset.percentage;
        form.querySelector('#tag-name').value = btn.dataset.name;
        form.querySelector('#tag-value').focus();
    }

    /**
     * Disable the form input.
     * @param {boolean} disable Whether to disable the form input.
     */
    disableFormInput(disable = true) {
        const form = document.querySelector('[data-region="tagform"]');
        if (!form) {
            return;
        }
        const input = form.querySelector('#tag-name');
        if (input) {
            input.disabled = disable;
        }
        const value = form.querySelector('#tag-value');
        if (value) {
            value.disabled = disable;
        }
        const submit = form.querySelector('[data-action="tag-confirm"]');
        if (submit) {
            submit.disabled = disable;
        }
    }
}
new TagManager();
