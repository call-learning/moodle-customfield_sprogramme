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
import Templates from 'core/templates';
import Repository from 'customfield_sprogramme/local/repository';
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
        const rendercontainer = await getProgramme(courseid);

        const modal = await Modal.create({
            large: true,
            title: getString('editprogramme', 'customfield_sprogramme'),
            body: rendercontainer,
            show: true,
        });

        modal.getModal().addClass('modal-customfield_sprogramme');
    });
};

/**
 * Get the disciplines.
 */


/**
 * Get the programme.
 * @param {String} courseid The courseid.
 * @return {Promise} The programme.
 */
const getProgramme = async(courseid) => {
    // Create the programme if it does not exist.
    let container = document.querySelector('.customfield_sprogramme');
    if (container) {
        return Promise.resolve(container);
    }
    const disciplines = await Repository.getDisciplines();
    const {html, js} = await Templates.renderForPromise('customfield_sprogramme/programme',
        {courseid: courseid, disciplines: disciplines});
    const rendercontainer = document.createElement('div');
    rendercontainer.classList.add('customfield_sprogramme');
    Templates.replaceNodeContents(rendercontainer, html, js);
    return rendercontainer;
};

export default {
    init: init,
};