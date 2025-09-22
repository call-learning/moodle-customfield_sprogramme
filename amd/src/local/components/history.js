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
 * @module     customfield_sprogramme/local/components/history
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import State from 'customfield_sprogramme/local/state';
import Templates from 'core/templates';

const stateTemplate = () => {
    const app = document.querySelector(`[data-region="history"]`);
    if (!app) {
        return;
    }
    const region = app.querySelector(`[data-region="modulesstatic"]`);
    if (!region) {
        return;
    }
    const template = `customfield_sprogramme/table/moduleshistory`;
    const tableColumns = async() => {
        let context = State.getValue('history');
        // Clone the context to avoid issues with the same context in multiple templates.
        context = JSON.parse(JSON.stringify(context));
        region.innerHTML = await Templates.render(template, context);
    };
    State.subscribe('history', tableColumns);
};

const componentInit = () => {
    stateTemplate();
};

export default componentInit;
