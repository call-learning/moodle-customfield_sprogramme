# Programme customfield #

This plugin adds a new custom field type "Programme" to Moodle's custom fields used by local_envasyllabus
to display programme information in various places.

[![Code testing](https://github.com/call-learning/moodle-customfield_sprogramme/actions/workflows/code-test.yml/badge.svg)](https://github.com/call-learning/moodle-customfield_sprogramme/actions/workflows/code-test.yml)

[![Moodle Plugin CI](https://github.com/call-learning/moodle-customfield_sprogramme/actions/workflows/ci.yml/badge.svg)](https://github.com/call-learning/moodle-customfield_sprogramme/actions/workflows/ci.yml)


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/customfield/field/sprogramme

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2025 CALL Learning <laurent@call-learning.fr>
2025 Bas Brands <bas@sonsbeekmedia.nl>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
