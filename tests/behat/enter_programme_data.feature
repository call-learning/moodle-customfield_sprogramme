@customfield @customfield_sprogramme @javascript
Feature: Testing enter_programme_data in customfield_sprogramme

  Background:
    Given the following "courses" exist:
      | fullname            | shortname | enablecompletion |
      | Syllabus Course 1   | SYLL1     | 1                |
    And the following "custom field categories" exist:
      | name          | component   | area   | itemid |
      | Course fields | core_course | course | 0      |
    And the following "custom fields" exist:
      | name             | category      | type       | shortname     | description |
      | SProgramme field | Course fields | sprogramme | sprogramme    | SProgramme  |
    And the following "users" exist:
      | username  | firstname | lastname | email                 |
      | teacher1  | Teacher   | One      | teacher1@example.com  |
      | teacher2  | Teacher   | Two      | teacher2@example.com |
      | student1  | Student   | One      | student1@example.com  |
      | student2  | Student   | Two      | student2@example.com  |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | SYLL1  | editingteacher |
      | teacher2  | SYLL1  | teacher        |
      | student1  | SYLL1  | student        |
      | student2  | SYLL1  | student        |

    Scenario: Enter programme data
      Given I log in as "teacher1"
        And I am on "SYLL1" course homepage
        And I navigate to "Settings" in current page administration
        And I expand all fieldsets
        And I click on "Edit programme" "link"
        And I wait "2" seconds
        And I should see "CCT / EPT"
        When I set mod "1" row "1" column "Intitulé de la séance / de l’exercice" to "Séance 1"
        And I set mod "1" row "1" column "CM" to "2.1"
        And I set mod "1" row "1" column "Perso av" to "7.7"
        And I set mod "1" row "1" column "Consignes de travail pour préparer la séance" to "Lorum Ipsum"
        And I set mod "1" row "1" column "Supports pédagogiques essentiels" to "Dolor sit Amet"
        Then I should see mod "1" row "1" column "Intitulé de la séance / de l’exercice" with value "Séance 1"

    Scenario: Add and remove programme rows
      Given I log in as "teacher1"
        And I am on "SYLL1" course homepage
        And I navigate to "Settings" in current page administration
        And I expand all fieldsets
        And I click on "Edit programme" "link"
        And I wait "2" seconds
        And I should see "CCT / EPT"
        When I set mod "1" row "1" column "Intitulé de la séance / de l’exercice" to "Séance 1"
        And I add a new row to mod "1"
        And I wait "2" seconds
        And I set mod "1" row "2" column "Intitulé de la séance / de l’exercice" to "Séance 2"
        And I set mod "1" row "2" column "TD" to "2.2"
        Then I should see mod "1" row "2" column "Intitulé de la séance / de l’exercice" with value "Séance 2"

    Scenario: Check and approve a teacher RFC
      Given I log in as "teacher1"
        And I am on "SYLL1" course homepage
        And I navigate to "Settings" in current page administration
        And I expand all fieldsets
        And I click on "Edit programme" "link"
        And I wait "2" seconds
        And I should see "CCT / EPT"
        And I add a new row to mod "1"
        And I wait "3" seconds
        When I set mod "1" row "1" column "Intitulé de la séance / de l’exercice" to "Séance 1"
        And I set mod "1" row "1" column "CM" to "2.1"
        And I set mod "1" row "1" column "Perso av" to "7.7"
        And I set mod "1" row "1" column "Consignes de travail pour préparer la séance" to "Lorum Ipsum"
        And I set mod "1" row "1" column "Supports pédagogiques essentiels" to "Dolor sit Amet"
        And I set mod "1" row "2" column "Intitulé de la séance / de l’exercice" to "Séance 2"
        And I set mod "1" row "2" column "TD" to "3"
        And I wait "3" seconds
        Then I should see mod "1" row "1" column "Intitulé de la séance / de l’exercice" with value "Séance 1"
        And I should see mod "1" row "2" column "TD" with value "3"
