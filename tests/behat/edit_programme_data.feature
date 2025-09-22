@customfield @customfield_sprogramme @javascript
Feature: As a teacher I can edit a Programme data in customfield_sprogramme

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

  Scenario: Enter programme data
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click on "Edit Programme" "link"
    And I should see "DD / RSE"
    When I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I set mod "1" row "1" column "Perso av" to "7.7"
    And I set mod "1" row "1" column "Instructions to prepare for the session" to "Lorum Ipsum"
    And I set mod "1" row "1" column "Essential teaching materials" to "Dolores sit Amet"
    Then I click on "Save" "button" in the "Edit Programme" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance 1"
    And I should see mod "1" row "1" column "Perso av" with value "7.7"
    And I should see "Submit change request" in the "Edit Programme" "dialogue"
    And I should see "Reset all changes" in the "Edit Programme" "dialogue"
    And I should not see "Save" in the "Edit Programme" "dialogue"

  Scenario: Add and remove programme rows
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click on "Edit Programme" "link"
    And I should see "DD / RSE"
    When I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "Session title or exercise" to "Séance 2"
    And I set mod "1" row "2" column "TD" to "2.2"
    Then I click on "Save" "button" in the "Edit Programme" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance 1"
    And I should see mod "1" row "2" column "Session title or exercise" with value "Séance 2"

  Scenario: Add Competencies
    Given I log in as "teacher1"
    And I am on "SYLL1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "SProgramme field enabled" to "1"
    And I click on "Save and display" "button"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click on "Edit Programme" "link"
    And I should see "DD / RSE"
    When I set mod "1" row "1" column "Session title or exercise" to "Séance 1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I add a new row to mod "1"
    And I set mod "1" row "2" column "Session title or exercise" to "Séance 2"
    And I set mod "1" row "2" column "TD" to "2.2"
    And I click on mod "1" row "2" "addcomp" "button"
    And I click on "COPREV1 - Évaluer l'état général, le bien-être et l'état nutritionnel d'un animal ou d'un groupe d'animaux" "link"
    And I click on "Add" "button" in the "tagform" "customfield_sprogramme > Competencies Form"
    And I click on "Save" "button" in the "tagform" "customfield_sprogramme > Competencies Form"
    Then I click on "Save" "button" in the "Edit Programme" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance 1"
    And I should see mod "1" row "1" column "Competencies" with value "COPREV1 - Évaluer l'état général, le bien-être et l'état nutritionnel d'un animal ou d'un groupe d'animaux"
