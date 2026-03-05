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
 * TODO describe module programme_form
 *
 * @module     customfield_sprogramme/programme_form
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import Notification from "core/notification";
import Repository from "./local/repository";

const initVisaForm = (manager) => {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-action="addvisa"]')) {
            return;
        }
        const button = event.target.closest('[data-action="addvisa"]');
        event.preventDefault();
        const rfcid = button.dataset.rfcid;
        showVisaForm(rfcid, manager);
    });
};

/**
 * Init the visa form.
 *
 * @param {number} rfcid
 * @param {Object} manager
 */
const showVisaForm = (rfcid, manager) => {
    const modalForm = new ModalForm({
        modalConfig: {
            title: getString('addvisa', 'customfield_sprogramme'),
        },
        formClass: '\\customfield_sprogramme\\local\\form\\visa_validate_form',
        args: {
            rfcid: rfcid,
        },
        saveButtonText: getString('save'),
    });
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, async(event) => {
        if (event.detail.result) {
            let response;
            if (event.detail.statuscode === 'approved') {
                response = await Repository.acceptVisa({
                    rfcid: rfcid, comment: event.detail.comment
                });
            } else if (event.detail.statuscode === 'deleted') {
                response = await Repository.removeVisa({
                    rfcid: rfcid
                });
            } else {
                response = await Repository.rejectVisa({
                    rfcid: rfcid, comment: event.detail.comment
                });
            }
            if (response) {
                manager.getTableData();
            }
        } else {
            Notification.addNotification({
                type: 'error',
                message: event.detail.errors.join('<br>')
            });
        }
    });
    modalForm.show();
};

export default initVisaForm;
