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
import './local/components/history';

/**
 * Initialise the history module.
 */
class History {

    /**
     * Constructor.
     * @param {HTMLElement} element The element.
     * @param {Int} courseid The courseid.
     * @param {Int} adminid The adminid.
     * @return {void}
     */
    constructor(element, courseid, adminid) {
        this.element = element;
        this.courseid = courseid;
        this.adminid = adminid;
        this.getProgrammeHistory();
    }

    /**
     * Get the stored programme history for this adminid.
     */
    async getProgrammeHistory() {
        const context = {
            courseid: this.courseid,
            adminid: this.adminid,
        };
        try {
            const data = await Repository.getProgrammeHistory(context);
            if (data.length === 0) {
                State.setValue('history', []);
            }
            State.setValue('history', data);
        } catch (error) {
            Notification.exception(error);
        }
    }
}

/*
 * Initialise
 *
 * @param {HTMLElement} element The element.
 * @param {Int} courseid The courseid.
 * @
 */
const init = (element, courseid, adminid) => {
    new History(element, courseid, adminid);
};

export default {
    init: init,
};