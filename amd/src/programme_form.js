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

const init = () => {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-action="programme-upload-form"]')) {
            return;
        }
        const button = event.target.closest('[data-action="programme-upload-form"]');
        event.preventDefault();

        const modalForm = new ModalForm({
            modalConfig: {
                title: getString('editsection'),
            },
            formClass: '\\customfield_sprogramme\\local\\form\\programme_upload_form',
            args: {
                ...button.dataset,
                currenturl: window.location.href,
            },
            saveButtonText: getString('save'),
        });
        modalForm.show();
    });
};

init();
