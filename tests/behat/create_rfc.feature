@customfield @customfield_sprogramme @javascript
Feature: As a teacher I can create and manage RFCs (Request for Change) in customfield_sprogramme

  Background:
    Given the following "courses" exist:
      | fullname          | shortname | enablecompletion |
      | Syllabus Course 1 | SYLL1     | 1                |
    And the following "custom field categories" exist:
      | name          | component   | area   | itemid |
      | Course fields | core_course | course | 0      |
    And the following "custom fields" exist:
      | name             | category      | type       | shortname  | description |
      | SProgramme field | Course fields | sprogramme | sprogramme | SProgramme  |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | SYLL1  | editingteacher |

  Scenario: Create and submit an RFC
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I set mod "1" row "1" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance 1"
    And I should see mod "1" row "1" column "TD" with value "3.5"
    And mod "1" row "1" column "TD" should not be editable
    And I should see "Submit change request" in the "Edit" "dialogue"
    And I should see "Save" in the "Edit" "dialogue"
    And I click on the "submitrfc" data action
    And I should see "Cancel change request" in the "Edit" "dialogue"

  Scenario: Cancel a submitted RFC
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on the "submitrfc" data action
    And I should see "Cancel change request" in the "Edit" "dialogue"
    And "[data-action='cancelrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I click on the "cancelrfc" data action
    Then mod "1" row "1" column "TD" should be editable
    And I should see "Save" in the "Edit" "dialogue"

  Scenario: Cancel a submitted RFC then edit and resubmit
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on the "submitrfc" data action
    And I click on the "cancelrfc" data action
    # Edit the data after canceling the RFC
    And I set mod "1" row "1" column "TD" to "4.0"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "CM" to "1.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "TD" with value "4.0"
    And I should see mod "1" row "2" column "CM" with value "1.5"
    And I click on the "submitrfc" data action
    And I should see mod "1" row "1" column "TD" with value "4.0"
    And I should see mod "1" row "2" column "CM" with value "1.5"
    And I should see "Cancel change request" in the "Edit" "dialogue"
    And "[data-action='cancelrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "TD" with value "4.0"
    And I should see mod "1" row "2" column "CM" with value "1.5"

  Scenario: Save and re-edit before submitting
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see "Continue editing" in the "Edit" "dialogue"
    And "[data-action='cancelrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I click on the "cancelrfc" data action
    Then mod "1" row "1" column "TD" should be editable
    And I set mod "1" row "1" column "TD" to "5.0"
    And I set mod "1" row "1" column "Perso av" to "2.3"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "TD" with value "5.0"
    And I should see mod "1" row "1" column "Perso av" with value "2.3"

  Scenario: Admin accepts a submitted RFC
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on the "submitrfc" data action
    And I should see "Cancel change request" in the "Edit" "dialogue"
    And I log out
    # Admin reviews the RFC
    And I log in as "admin"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And "[data-action='acceptrfc']" "css_element" should exist in the "Edit" "dialogue"
    And "[data-action='rejectrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I click on the "acceptrfc" data action
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance 1"
    And I should see mod "1" row "1" column "CM" with value "2.1"
    And I should see mod "1" row "2" column "TD" with value "3.5"
    And "[data-action='acceptrfc']" "css_element" should not exist in the "Edit" "dialogue"
    And "[data-action='rejectrfc']" "css_element" should not exist in the "Edit" "dialogue"

  Scenario: Admin rejects a submitted RFC then teacher re-edits and resubmits
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "TD" to "3.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on the "submitrfc" data action
    And I log out
    # Admin rejects the RFC
    And I log in as "admin"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And "[data-action='acceptrfc']" "css_element" should exist in the "Edit" "dialogue"
    And "[data-action='rejectrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I click on the "rejectrfc" data action
    And I log out
    # Teacher sees cancel and submit options, cancels and re-edits
    And I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And "[data-action='cancelrfc']" "css_element" should exist in the "Edit" "dialogue"
    And "[data-action='submitrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I click on the "cancelrfc" data action
    Then mod "1" row "1" column "TD" should be editable
    # Edit and save new values
    And I set mod "1" row "1" column "TD" to "4.0"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "CM" to "1.5"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "TD" with value "4.0"
    And I should see mod "1" row "2" column "CM" with value "1.5"
    And I click on the "submitrfc" data action
    And I log out
    # Admin sees accept and reject links again
    And I log in as "admin"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And "[data-action='acceptrfc']" "css_element" should exist in the "Edit" "dialogue"
    And "[data-action='rejectrfc']" "css_element" should exist in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "TD" with value "4.0"
    And I should see mod "1" row "2" column "CM" with value "1.5"
