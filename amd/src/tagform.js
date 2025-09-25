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
 * Tag Form helpers.
 *
 * @module     customfield_sprogramme/tagform
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class TagForm {

    /**
     * Constructor.
     */
    constructor() {
        this.addEventListeners();
    }

    /**
     * Add event listeners to the form.
     */
    addEventListeners() {
        const form = document.querySelector('[data-region="tagform"]');
        if (!form) {
            return;
        }
        if (form.dataset.initialised) {
            // The form is already initialised, no need to add event listeners again.
            return;
        }
        form.dataset.initialised = true;

        // Handle the form submission.
        const search = form.querySelector('input[type="search"]');
        search.addEventListener('input', (e) => {
            const input = e.target.closest('input');
            if (input) {
                this.typeahead(input);
            }
        });
    }

    /**
     * Typeahead.
     *
     * @param {object} input The input that was changed.
     * @return {void}
     */
    typeahead(input) {
        const value = input.value;
        const form = document.querySelector('[data-region="tagform"]');
        const options = form.querySelectorAll('[data-option]');
        options.forEach(option => {
            this.removeMatchBold(option);
            if (option.textContent.toLowerCase().includes(value.toLowerCase())) {
                option.classList.remove('d-none');
                this.makeMatchBold(option, value);
            } else {
                option.classList.add('d-none');
            }
        });
    }

    /**
     * Remove the match bold.
     * @param {object} option The option.
     * @return {void}
     */
    removeMatchBold(option) {
        option.innerHTML = option.textContent;
    }


    /**
     * Make the match bold.
     * @param {object} option The option.
     * @param {string} value The value.
     * @return {void}
     */
    makeMatchBold(option, value) {
        const text = option.textContent;
        const index = text.toLowerCase().indexOf(value.toLowerCase());
        const first = text.slice(0, index);
        const match = text.slice(index, index + value.length);
        const last = text.slice(index + value.length);
        option.innerHTML = first + '<strong>' + match + '</strong>' + last;
    }
}

const init = () => {
    new TagForm();
    // Ensure the tag form is initialised.
};

export default {
    init: init
};
