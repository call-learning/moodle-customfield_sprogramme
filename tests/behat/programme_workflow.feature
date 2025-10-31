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
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | SYLL1  | editingteacher |
      | teacher2 | SYLL1  | editingteacher |
    And the programme custom field "sprogramme" is enabled for course "SYLL1"

  Scenario: Teacher 1 enters programme data and saves it as a draft, teacher 2 should be able to edit the programme also and no change made to the programme.
    Given I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher2"
    And I set mod "1" row "1" column "CM" to "2.2"
    Then I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance teacher2"
    And I log out
    And I am on the "SYLL1" course page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance admin"
    And I set mod "1" row "1" column "CM" to "2.2"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I should see mod "1" row "1" column "Session title or exercise" with value "Séance admin"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    Then I should see mod "1" row "1" column "Session title or exercise" with value "Séance teacher1"
    And I log out


  Scenario: Teacher 1 enters programme data then submit for approval, teacher 2 should not be able to edit and when the admin accept it,
    teacher 2 should be able to see the changes.
    Given I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on "[data-action='submitrfc']" "css" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should see "Séance teacher1" in the "Edit" "dialogue"
    And I should see "Change request by Teacher One" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher1" in the "Edit" "dialogue"
    And I click on "[data-action='acceptrfc']" "css" in the "Edit" "dialogue"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    Then I should see mod "1" row "1" column "Session title or exercise" with value "Séance teacher1"
    And I log out

  Scenario: Teacher 1 enters programme data then submit for approval, admin reject it, modify it, Teacher 1 should be able to submit it again and admin able to accept it.
    Given I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on "[data-action='submitrfc']" "css" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should see "Séance teacher1" in the "Edit" "dialogue"
    And I should see "Change request by Teacher One" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should see "Séance teacher1" in the "Edit" "dialogue"
    And I click on "[data-action='rejectrfc']" "css" in the "Edit" "dialogue"
    And I set mod "1" row "1" column "Session title or exercise" to "Séance admin"
    And I set mod "1" row "1" column "CM" to "2.2"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    Then I should see mod "1" row "1" column "Session title or exercise" with value "Séance admin"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    Then I should see mod "1" row "1" column "Session title or exercise" with value "Séance teacher1"
    And I should see "Reset all changes" in the "Edit" "dialogue"
    And I log out


  Scenario: Teacher 1 enters programme data then submit for approval, admin reject it, Teacher 1 should be able to remove it and edit again.
    Given I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on "[data-action='submitrfc']" "css" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher"
    And I should see "Change request by Teacher One" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher1" in the "Edit" "dialogue"
    And I click on "[data-action='rejectrfc']" "css" in the "Edit" "dialogue"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    Then I should see mod "1" row "1" column "Session title or exercise" with value "Séance teacher1"
    And I click on "[data-action='removerfc']" "css" in the "Edit" "dialogue"
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1 modified"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher1 modified" in the "Edit" "dialogue"
    And I log out

  Scenario: Teacher 1 enters programme data then submit for approval, it should be able to cancel the approval request, teacher 2 should then be able to edit it.
    Given I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I set mod "1" row "1" column "Session title or exercise" to "Séance teacher1"
    And I set mod "1" row "1" column "CM" to "2.1"
    And I click on "Save" "button" in the "Edit" "dialogue"
    And I click on "[data-action='submitrfc']" "css" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should not see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher"
    And I should see "Change request by Teacher One" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher1"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I click on "[data-action='cancelrfc']" "css" in the "Edit" "dialogue"
    And I log out
    And I am on the "SYLL1" course page logged in as "teacher2"
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click the programme edit button
    And I should see "Save" in the "Edit" "dialogue"
    And I should not see "Séance 1 teacher"
    And I should not see "Change request by Teacher One" in the "Edit" "dialogue"
    And I log out
