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
 * TODO describe module formfield
 *
 * @module     customfield_sprogramme/formfield
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Manager from 'customfield_sprogramme/manager';
import {getString} from 'core/str';
import Modal from 'core/modal';
/*
 * Initialise
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 */
const init = (element, courseid) => {
    element.addEventListener('click', async(event) => {
        event.preventDefault();

        await getProgramme(element, courseid);
        const modalContent = document.querySelector('[data-region="app"]');

        const modal = await Modal.create({
            large: true,
            title: getString('editprogramme', 'customfield_sprogramme'),
            body: modalContent,
            show: true,
        });

        modal.getModal().addClass('modal-customfield_sprogramme');
    });
};


/**
 * Get the programme.
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 * @return {Promise} The programme.
 */
const getProgramme = async(element, courseid) => {
    Manager.init(element, courseid);
};

export default {
    init: init,
};