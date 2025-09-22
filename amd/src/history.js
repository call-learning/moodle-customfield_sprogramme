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
 * TODO describe module history
 *
 * @module     customfield_sprogramme/history
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import State from 'customfield_sprogramme/local/state';
import Repository from 'customfield_sprogramme/local/repository';
import Notification from 'core/notification';
import componentInit from './local/components/history';

/**
 * Initialise the history module.
 */
class History {

    /**
     * Constructor.
     * @param {Int} rfcid The rfcid.
     * @param {Int} datafieldid The datafieldid.
     * @return {void}
     */
    constructor(rfcid, datafieldid) {
        this.rfcid = rfcid;
        this.datafieldid = datafieldid;
        this.getProgrammeHistory();
    }

    /**
     * Get the stored programme history for this adminid.
     */
    async getProgrammeHistory() {
        const context = {
            rfcid: this.rfcid,
            datafieldid: this.datafieldid,
        };
        try {
            const response = await Repository.getProgrammeHistory(context);
            if (response.length === 0) {
                State.setValue('history', []);
            }
            const history = {
                'modules': this.parseModules(response),
                'columns': response.columns,
                'rfcs': response.rfcs,
            };
            State.setValue('history', history);
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
            mod.rows.map(row => {
                row.cells = row.cells.map(cell => {
                    const column = response.columns.find(column => column.column == cell.column);
                    // Clone the column properties to the cell but keep the cell properties.
                    cell = Object.assign({}, cell, column);
                    cell.changed = cell.value !== cell.oldvalue;
                    return cell;
                });
                return row;
            });
        });
        return response.modules;
    }
}

/*
 * Initialise
 *
 * @param {Int} rfcid The rfcid.
 * @param {Int} datafieldid The datafieldid.
 */
const init = (rfcid, datafieldid) => {
    componentInit();
    return new History(rfcid, datafieldid);
};

export default {
    init: init,
};