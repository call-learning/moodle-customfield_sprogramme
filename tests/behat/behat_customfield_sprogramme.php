<?php
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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

/**
 * Behat steps in plugin customfield_sprogramme
 *
 * @package    customfield_sprogramme
 * @category   test
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_customfield_sprogramme extends behat_base {
    /**
     * Return the list of partial named selectors.
     *
     * @return array
     */
    public static function get_partial_named_selectors(): array {
        return [
            new behat_component_named_selector(
                'Competencies Form',
                [
                    "//*[@data-region=%locator%][@data-type='competencies']",
                ],
            ),
        ];
    }

    /**
     * Sets a specific cell in modulenr x , in rownr x, in a columnname to a specific value.
     *
     * @Given /^I set mod "(?P<modulenr_string>(?:[^"]|\\")*)" row "(?P<rownr_string>(?:[^"]|\\")*)" column "(?P<columnname_string>(?:[^"]|\\")*)" to "(?P<value_string>(?:[^"]|\\")*)"$/
     * @param string $modulenr The module number
     * @param string $rownr The row number
     * @param string $columnname The column name
     * @param string $value The value to set
     * @throws ExpectationException
     */
    public function set_cell_value_to($modulenr, $rownr, $columnname, $value) {
        $cell = $this->find_cell($modulenr, $rownr, $columnname);
        $cellinput = $cell->find('css', 'input, select, textarea');
        if (!$cellinput) {
            throw new ExpectationException(
                'Cell input (field) not found in row ' . $rownr . ' and column ' . $columnname,
                $this->getSession()
            );
        }
        $fieldinstance = behat_field_manager::get_field_instance('field', $cellinput, $this->getSession());
        // Set the value.
        $fieldinstance->set_value($value);
    }

    /**
     * Checks the value of a specific cell in modulenr x , in rownr x, in a columnname.
     *
     * @Then /^I should see mod "(?P<modulenr_string>(?:[^"]|\\")*)" row "(?P<rownr_string>(?:[^"]|\\")*)" column "(?P<columnname_string>(?:[^"]|\\")*)" with value "(?P<value_string>(?:[^"]|\\")*)"$/
     * @param string $modulenr The module number
     * @param string $rownr The row number
     * @param string $columnname The column name
     * @param string $value The value to check
     * @throws ExpectationException
     */
    public function check_cell_value($modulenr, $rownr, $columnname, $value) {
        $cell = $this->find_cell($modulenr, $rownr, $columnname);
        $cellinput = $cell->find('css', 'input, select, textarea');
        if (!$cellinput) {
            // Maybe it is a readonly field, so just check the text.
            if ($newvalue = $cell->find('css', '.newvalue')) {
                $cellvalue = trim($newvalue->getText());
            } else {
                $cellvalue = trim($value);
            }
        } else {
            $fieldinstance = behat_field_manager::get_field_instance('field', $cellinput, $this->getSession());
            // Check if the value is set.
            $cellvalue = $fieldinstance->get_value();
        }
        if ($cellvalue != $value) {
            throw new ExpectationException('Cell value ' . $cellvalue . ' is not set to ' . $value, $this->getSession());
        }
    }

    /**
     * Adds a new module by clicking the add module button (data-action="addmodule").
     *
     * @Given /^I add a new module$/
     * @throws ExpectationException
     */
    public function add_module() {
        // Find the app.
        $app = $this->find('css', '[data-region="app"]');

        // Find the add module button.
        $addmodulebutton = $app->find('css', '[data-action="addmodule"]');
        if (empty($addmodulebutton)) {
            throw new ElementNotFoundException($this->getSession(), 'add module button', 'css', '[data-action="addmodule"]');
        }
        // Click the add module button.
        $addmodulebutton->click();
    }

    /**
     * Adds a new row to the module by clicking the add button (data-action="addrow").
     *
     * @Given /^I add a new row to mod "(?P<modulenr_string>(?:[^"]|\\")*)"$/
     * @param string $modulenr The module number
     * @throws ExpectationException
     */
    public function add_row($modulenr) {
        // App.
        $app = $this->find('css', '[data-region="app"]');
        // There can be multiple modules in the app identified by [data-region="module"].
        // The modulenr corresponds with the order of the modules in the app.
        $modules = $app->findAll('css', '[data-region="module"]');
        if (empty($modules)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }
        if ($modulenr > count($modules)) {
            throw new ExpectationException('Module number ' . $modulenr . ' is out of range. There are only ' . count($modules) .
                ' modules.', $this->getSession());
        }
        // Find the module.
        $module = $modules[$modulenr - 1];
        if (empty($module)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }
        // Count the number of rows in the module.
        $rows = $module->findAll('css', '[data-region="rows"]');

        // Find the add button in the module.
        $addbutton = $module->find('css', '[data-action="addrow"][data-id="-1"]');
        if (empty($addbutton)) {
            throw new ElementNotFoundException($this->getSession(), 'add button', 'css', '[data-action="addrow"][data-id="-1"]');
        }
        // Click the add button.
        $addbutton->click();
    }

    /**
     * Adds a new row to the module by clicking the add button (data-action="addrow").
     *
     * @Given /^I click on mod "(?P<modulenr_string>(?:[^"]|\\")*)" row "(?P<rownr_string>(?:[^"]|\\")*)" "(?P<element_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @param string $modulenr The module number
     * @param string $rownr The row number
     * @param string $locator The locator to find the element
     * @param string $selector The selector type (css or xpath)
     * @throws ExpectationException
     */
    public function click_on_mod_row($modulenr, $rownr, $locator, $selector) {
        $row = $this->find_row($modulenr, $rownr);
        $element = $this->find($selector, $locator,
            new ExpectationException('Element ' . $locator . ' not found in row ' . $rownr, $this->getSession()), $row);
        if (empty($element)) {
            throw new ElementNotFoundException($this->getSession(), 'element', $selector, $locator);
        }
        // Click the element.
        $element->click();
    }

    /**
     * Edits the course programme.
     *
     * @Given /^I edit the course programme for "(?P<coursename_string>(?:[^"]|\\")*)"$/
     * @param string $coursename The course name
     */
    public function i_edit_the_course_programme($coursename) {
        $this->execute('behat_general::i_am_on_course_homepage', [$coursename]);

        // Naviguer vers "Paramètres" dans l'administration de la page courante.
        $this->execute('behat_navigation::i_navigate_to_in_current_page_administration', ['Settings']);

        // Déplier tous les fieldsets.
        $this->execute('behat_general::i_expand_all_fieldsets');

        // Cliquer sur le lien "Edit Programme".
        $this->getSession()->getPage()->clickLink('Edit Programme');
    }

    /**
     * Enables the programme custom field for a course.
     * @Given /^the programme custom field "(?P<customfieldname_string>(?:[^"]|\\")*)" is enabled for course "(?P<coursename_string>(?:[^"]|\\")*)"$/
     *
     * @param $customfieldname
     * @param $coursename
     *
     * @return void
     * @throws ElementNotFoundException
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function i_enable_programme($customfieldname, $coursename) {
        $courseid = $this->get_course_id($coursename);
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $cfdata = $handler->get_instance_data($courseid, true);
        $fieldcontroller = null;
        foreach ($cfdata as $data) {
            if ($data->get_field()->get('shortname') === $customfieldname) {
                $fieldcontroller = $data;
                break;
            }
        }
        if (!$fieldcontroller) {
            throw new ElementNotFoundException('Custom field ' . $customfieldname . ' not found.');
        }
        $fieldcontroller->set($fieldcontroller->datafield(), 1);
        $fieldcontroller->set('contextid', context_course::instance($courseid)->id);
        $fieldcontroller->save();
    }

    /**
     * Sets the module name for a specific module.
     *
     * @Given /^I set mod "(?P<modulenr_string>(?:[^\"]|\\\\\")*)\" name to "(?P<name_string>(?:[^\"]|\\\\\")*)\"$/
     * @param string $modulenr The module number
     * @param string $name The module name to set
     * @throws ExpectationException
     */
    public function set_module_name($modulenr, $name) {
        // Find the module using the same pattern as find_cell.
        $baselocator = "//*[@data-region = 'app']//*[@data-region = 'module'][$modulenr]";
        $baseelement = $this->find('xpath', $baselocator);

        // Find the module name input within this module.
        $modulenameinput = $this->find(
            'css',
            'input[data-region="modulename"]',
            new ExpectationException('Module name input not found for module ' . $modulenr, $this->getSession()),
            $baseelement,
        );

        // Clear and set the new value.
        $modulenameinput->setValue($name);
    }

    /**
     * Deletes a specific module by clicking the delete button.
     *
     * @Given /^I delete mod "(?P<modulenr_string>(?:[^\"]|\\\\\")*)\"$/
     * @param string $modulenr The module number
     * @throws ExpectationException
     */
    public function delete_module($modulenr) {
        // Find the module using the same pattern as find_cell.
        $baselocator = "//*[@data-region = 'app']//*[@data-region = 'module'][$modulenr]";
        $baseelement = $this->find('xpath', $baselocator);

        // Find the delete button within this module.
        $deletebutton = $this->find(
            'css',
            'button[data-action="deletemodule"]',
            new ExpectationException('Delete button not found for module ' . $modulenr, $this->getSession()),
            $baseelement,
        );

        // Click the delete button.
        $deletebutton->click();
    }

    /**
     * Deletes a specific row within a module by clicking the delete button.
     *
     * @Given /^I delete mod "(?P<modulenr_string>(?:[^\"]|\\\\\")*)\" row "(?P<rownr_string>(?:[^\"]|\\\\\")*)\"$/
     * @param string $modulenr The module number
     * @param string $rownr The row number
     * @throws ExpectationException
     */
    public function delete_row($modulenr, $rownr) {
        // Find the specific row using the existing find_row method.
        $row = $this->find_row($modulenr, $rownr);

        // Find the delete button within this row.
        $deletebutton = $this->find(
            'css',
            'button[data-action="deleterow"]',
            new ExpectationException('Delete row button not found for module ' . $modulenr . ' row ' . $rownr, $this->getSession()),
            $row,
        );

        // Click the delete button.
        $deletebutton->click();
    }

    /**
     * Closes the editing form by clicking the close button.
     *
     * @Given /^I close the programme editing form$/
     * @throws ExpectationException
     */
    public function close_programme_editing_form() {
        // Find the close button.
        $closebutton = $this->find(
            'css',
            'button[data-action="closeform"]',
            new ExpectationException('Close form button not found', $this->getSession())
        );

        // Click the close button.
        $closebutton->click();
    }

    /**
     * Find an element in the app table.
     *
     * @param $modulenr
     * @param $rownr
     * @param $columnname
     * @return NodeElement
     * @throws ElementNotFoundException|ExpectationException
     */
    private function find_cell($modulenr, $rownr, $columnname) {
        // There can be multiple modules in the app identified by [data-region="module"].
        $baselocator = "//*[@data-region = 'app']//*[@data-region = 'module'][$modulenr]";
        $baseelement = $this->find('xpath', $baselocator);
        $column = $this->find(
            'xpath',
            "//*[@data-region = 'columns']/th/*[text() = '$columnname']",
            new ExpectationException('Column name ' . $columnname . ' not found.', $this->getSession()),
            $baseelement,
        );
        // Get the attribute data-column of the column.
        $columnnr = $column->getParent()->getAttribute('data-columnid');

        // Find the row in the modules rows in [data-region="rows"].
        $row = $this->find_row($modulenr, $rownr);

        // Find the cell.
        $cell = $this->find(
            'css',
            'td[data-columnid="' . $columnnr . '"]',
            new ExpectationException('Cell not found in row ' . $rownr . ' and column ' . $columnname, $this->getSession()),
            $row
        );
        return $cell;
    }

    /**
     * Find an element in the app table.
     *
     * @param $modulenr
     * @param $rownr
     * @param $columnname
     * @return NodeElement
     * @throws ElementNotFoundException|ExpectationException
     */
    private function find_row($modulenr, $rownr) {
        // There can be multiple modules in the app identified by [data-region="module"].
        $baselocator = "//*[@data-region = 'app']//*[@data-region = 'module'][$modulenr]";
        $baseelement = $this->find('xpath', $baselocator);

        // Find the row in the modules rows in [data-region="rows"].
        $row = $this->find(
            'xpath',
            "//*[@data-region = 'rows']/tr[$rownr]",
            new ExpectationException('Row number ' . $rownr . ' not found.', $this->getSession()),
            $baseelement,
        );

        return $row;
    }
}
