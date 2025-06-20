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
 * TODO describe module table
 *
 * @module     customfield_sprogramme/local/components/table
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import State from 'customfield_sprogramme/local/state';
import Templates from 'core/templates';

/**
 * Define the user navigation.
 * @param {String} type The type.
 * @param {String} templatename The template name.
 * @param {String} root region root.
 */
const stateTemplate = (type, templatename = '', root = 'app') => {
    const app = document.querySelector(`[data-region="${root}"]`);
    if (!app) {
        return;
    }
    const region = app.querySelector(`[data-region="${type}"]`);
    if (!region) {
        return;
    }
    if (templatename == '') {
        templatename = type;
    }
    const template = `customfield_sprogramme/table/${templatename}`;
    const tableColumns = async(context) => {
        if (context[type] === undefined) {
            return;
        }
        context[type] = State.getValue(type);
        // Clone the context to avoid issues with the same context in multiple templates.
        context = JSON.parse(JSON.stringify(context));
        const {html, js} = await Templates.renderForPromise(template, context);
        Templates.replaceNodeContents(region, html, js);
    };
    State.subscribe(type, tableColumns);
};

stateTemplate('columns', 'columnsheader');
stateTemplate('modules');
stateTemplate('modulesstatic', '', 'static');
stateTemplate('rfc');
stateTemplate('editbuttons', '', 'modalheader');