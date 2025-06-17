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
import History from 'customfield_sprogramme/history';
import {getString} from 'core/str';
import Modal from 'core/modal';

/*
 * Initialise
 * @param {HTMLElement} element The element.
 * @param {String} courseid The courseid.
 */
const init = async() => {
    document.addEventListener('click', async(event) => {
        const editaction = event.target.closest('[data-action="editprogramme"]');
        if (editaction) {
            const courseid = editaction.dataset.courseid;
            await getEditor(editaction, courseid);
            event.preventDefault();
        }
        const actionBtn = event.target.closest('[data-action="showrfc"]');
        if (actionBtn) {
            const rfcid = parseInt(actionBtn.dataset.rfcid);
            const courseid = actionBtn.dataset.courseid;
            await showHistory(rfcid, courseid);
            event.preventDefault();
        }
    });
};

const showHistory = async(rfcid, courseid) => {
    await getProgrammeHistory(rfcid, courseid);
    const modalContent = document.querySelector('[data-region="history"]');

    const modal = await Modal.create({
        large: true,
        title: getString('history', 'customfield_sprogramme'),
        body: modalContent,
        show: true,
    });
    const modalElement = modal.getModal()[0];
    modalElement.classList.add('modal-customfield_sprogramme_history');
};

const getEditor = async(element, courseid) => {
    await getProgramme(element, courseid);
    const modalContent = document.querySelector('[data-region="app"]');
    const modalHeader = document.querySelector('[data-region="modalheader"]');

    const modal = await Modal.create({
        large: true,
        title: getString('editprogramme', 'customfield_sprogramme'),
        body: modalContent,
        show: true,
    });

    const modalElement = modal.getModal()[0];
    modalElement.classList.add('modal-customfield_sprogramme_editor');
    const header = modalElement.querySelector('[data-region="header"]');
    const title = modalElement.querySelector('[data-region="title"]');
    if (title) {
        // Add the icone after the title Element.
        header.insertBefore(modalHeader, title.nextSibling);
    }

    document.addEventListener('closeform', () => {
        modal.hide();
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

/**
 * Get the programme history.
 * @param {int} rfcid The Rfc id.
 * @param {int} courseid The course id.
 */
const getProgrammeHistory = async(rfcid, courseid) => {
    History.init(rfcid, courseid);
};

export default {
    init: init,
};