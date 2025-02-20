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
import Templates from 'core/templates';
import {getString} from 'core/str';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Repository from 'customfield_sprogramme/local/repository';
/*
 * Initialise
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 */
const init = async(element, courseid) => {
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
        modal.getRoot().on(ModalEvents.hidden, () => {
            window.location.reload();
        });

        const saveButton = document.createElement('div');
        const modalElement = modal.getModal()[0];
        modalElement.classList.add('modal-customfield_sprogramme');
        const header = modalElement.querySelector('[data-region="header"]');
        const title = modalElement.querySelector('[data-region="title"]');
        if (title) {
            // Add the icone after the title Element.
            header.insertBefore(saveButton, title.nextSibling);
        }

        const {html, js} = await Templates.renderForPromise('customfield_sprogramme/table/savebutton', {courseid: courseid});
        await Templates.replaceNode(saveButton, html, js);
        const closeButton = header.querySelector('[data-action="closeform"]');
        closeButton.addEventListener('click', () => {
            modal.hide();
        });
        const downloadButton = header.querySelector('[data-action="programme-download-csv"]');
        downloadButton.addEventListener('click', async(event) => {
            downloadCSV(courseid);
            event.preventDefault();
        });
        const renderedSaveButton = header.querySelector('[data-action="saveconfirm"]');
        renderedSaveButton.addEventListener('click', async(event) => {
            // Run the 'saveconfirm' custom event.
            const saveEvent = new CustomEvent('saveconfirm', {bubbles: true});
            document.dispatchEvent(saveEvent);
            event.preventDefault();
        });

    });
};

const downloadCSV = async(courseid) => {
    const csv = await Repository.csvData({courseid: courseid});
    const blob = new Blob([csv.csv], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = csv.filename;
    a.click();
    window.URL.revokeObjectURL(url);
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