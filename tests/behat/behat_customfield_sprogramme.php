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

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException as ExpectationException;

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
        // App
        $app = $this->find('css', '[data-region="app"]');
        // There can be multiple modules in the app identified by [data-region="module"].
        // The modulenr corresponds with the order of the modules in the app.
        $modules = $app->findAll('css', '[data-region="module"]');
        if (empty($modules)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }
        if ($modulenr > count($modules)) {
            throw new ExpectationException('Module number ' . $modulenr . ' is out of range. There are only ' . count($modules) . ' modules.', $this->getSession());
        }
        // Find the module.
        $module = $modules[$modulenr - 1];
        if (empty($module)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }

        // Find the row in the modules rows in [data-region="rows"].
        $rows = $module->findAll('css', '[data-region="rows"]');
        if (empty($rows)) {
            throw new ElementNotFoundException($this->getSession(), 'rows', 'css', '[data-region="rows"]');
        }
        if ($rownr > count($rows)) {
            throw new ExpectationException('Row number ' . $rownr . ' is out of range. There are only ' . count($rows) . ' rows.', $this->getSession());
        }
        // Find the row.
        // The rownr corresponds with the order of the rows in the module.
        $row = $rows[$rownr - 1];
        if (empty($row)) {
            throw new ElementNotFoundException($this->getSession(), 'row', 'css', '[data-region="rows"]');
        }

        // Find the column with columnname.
        $columnrow = $module->find('css', '[data-region="columns"]');
        $columns = $columnrow->findAll('css', 'th');
        if (empty($columns)) {
            throw new ElementNotFoundException($this->getSession(), 'columns', 'css', '[data-region="columns"]');
        }
        $column = null;
        foreach ($columns as $col) {
            if ($col->getText() == $columnname) {
                $column = $col;
                break;
            }
        }
        if (empty($column)) {
            throw new ElementNotFoundException($this->getSession(), 'column', 'css', '[data-region="columns"]');
        }
        // Get the attribute data-column of the column.
        $columnnr = $column->getAttribute('data-columnid');


        // Find the cell.
        $cell = $row->find('css', 'td[data-columnid="' . $columnnr . '"] input');
        if (empty($cell)) {
            throw new ElementNotFoundException($this->getSession(), 'cell', 'css', 'td:nth-child(' . $columnnr . ')');
        }
        // Set the value.
        $cell->setValue($value);
        // Check if the value is set.
        $cellvalue = $cell->getValue();
        if ($cellvalue != $value) {
            throw new ExpectationException('Cell value ' . $cellvalue . ' is not set to ' . $value, $this->getSession());
        }
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
        // App
        $app = $this->find('css', '[data-region="app"]');
        // There can be multiple modules in the app identified by [data-region="module"].
        // The modulenr corresponds with the order of the modules in the app.
        $modules = $app->findAll('css', '[data-region="module"]');
        if (empty($modules)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }
        if ($modulenr > count($modules)) {
            throw new ExpectationException('Module number ' . $modulenr . ' is out of range. There are only ' . count($modules) . ' modules.', $this->getSession());
        }
        // Find the module.
        $module = $modules[$modulenr - 1];
        if (empty($module)) {
            throw new ElementNotFoundException($this->getSession(), 'module', 'css', '[data-region="module"]');
        }

        // Find the row in the modules rows in [data-region="rows"].
        $rows = $module->findAll('css', '[data-region="rows"]');
        if (empty($rows)) {
            throw new ElementNotFoundException($this->getSession(), 'rows', 'css', '[data-region="rows"]');
        }
        if ($rownr > count($rows)) {
            throw new ExpectationException('Row number ' . $rownr . ' is out of range. There are only ' . count($rows) . ' rows.', $this->getSession());
        }
        // Find the row.
        // The rownr corresponds with the order of the rows in the module.
        $row = $rows[$rownr - 1];
        if (empty($row)) {
            throw new ElementNotFoundException($this->getSession(), 'row', 'css', '[data-region="rows"]');
        }

        // Find the column with columnname.
        $columnrow = $module->find('css', '[data-region="columns"]');
        $columns = $columnrow->findAll('css', 'th');
        if (empty($columns)) {
            throw new ElementNotFoundException($this->getSession(), 'columns', 'css', '[data-region="columns"]');
        }
        $column = null;
        foreach ($columns as $col) {
            if ($col->getText() == $columnname) {
                $column = $col;
                break;
            }
        }
        if (empty($column)) {
            throw new ElementNotFoundException($this->getSession(), 'column', 'css', '[data-region="columns"]');
        }
        // Get the attribute data-column of the column.
        $columnnr = $column->getAttribute('data-columnid');
        // Find the cell.
        $cell = $row->find('css', 'td[data-columnid="' . $columnnr . '"] input');
        if (empty($cell)) {
            throw new ElementNotFoundException($this->getSession(), 'cell', 'css', 'td:nth-child(' . $columnnr . ')');
        }
        // Check if the value is set.
        $cellvalue = $cell->getValue();
        if ($cellvalue != $value) {
            throw new ExpectationException('Cell value ' . $cellvalue . ' is not set to ' . $value, $this->getSession());
        }
    }

}
