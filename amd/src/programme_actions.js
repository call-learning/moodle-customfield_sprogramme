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
import $ from 'jquery';

/*
 * Initialise
 * @param {HTMLElement} element The element.
 * @param {String} datafieldid The datafieldid.
 */
const init = async() => {
    document.addEventListener('click', async(event) => {
        const editaction = event.target.closest('[data-action="editprogramme"]');
        if (editaction) {
            const datafieldid = editaction.dataset.datafieldid;
            await getEditor(editaction, datafieldid);
            event.preventDefault();
        }
        const actionBtn = event.target.closest('[data-action="showrfc"]');
        if (actionBtn) {
            const rfcid = parseInt(actionBtn.dataset.rfcid);
            const datafieldid = actionBtn.dataset.datafieldid;
            await showHistory(rfcid, datafieldid);
            event.preventDefault();
        }
        const popOvers = document.querySelectorAll('[data-toggle="popover"]');
        const currentPopover = event.target.closest('[data-toggle="popover"]');
        if (popOvers.length > 0) {
            popOvers.forEach((popover) => {
                if (popover !== currentPopover) {
                    $(popover).popover('hide');
                }
            });
        }
    });
};

/**
 * Show the history modal.
 *
 * @param {Number} rfcid
 * @param {Number} datafieldid
 * @return {Promise<void>}
 */
const showHistory = async(rfcid, datafieldid) => {
    await getProgrammeHistory(rfcid, datafieldid);
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

class CustomModal extends Modal {
    hide() {
        // Do not close the modal unless we do it explicitly.
    }
    close() {
        super.hide();
    }
}

/**
 * Get the editor modal.
 * @param {HTMLElement} element
 * @param {Number} datafieldid
 * @return {Promise<void>}
 */
const getEditor = async(element, datafieldid) => {
    await getProgramme(element, datafieldid);
    const modalContent = document.querySelector('[data-region="app"]');
    const modalHeader = document.querySelector('[data-region="modalheader"]');

    const modal = await CustomModal.create({
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
        modal.close();
    });
};

/**
 * Get the programme.
 * @param {HTMLElement} element The element.
 * @param {String} datafieldid The datafieldid.
 * @return {Promise} The programme.
 */
const getProgramme = async(element, datafieldid) => {
    Manager.init(element, datafieldid);
};

/**
 * Get the programme history.
 * @param {Number} rfcid The Rfc id.
 * @param {Number} datafieldid The course id.
 */
const getProgrammeHistory = async(rfcid, datafieldid) => {
    History.init(rfcid, datafieldid);
};

export default {
    init: init,
};