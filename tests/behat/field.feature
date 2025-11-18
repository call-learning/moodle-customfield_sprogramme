@customfield @customfield_sprogramme @javascript
Feature: Managers can manage course custom fields sprogramme
  In order to have additional data on the course
  As a manager
  I need to create, edit, remove and sort custom fields

  Background:
    Given the following "custom field categories" exist:
      | name              | component   | area   | itemid |
      | Category for test | core_course | course | 0      |
    And I log in as "admin"
    And I navigate to "Courses > Default settings > Course custom fields" in site administration

  Scenario: Create a custom course sprogramme field
    When I click on "Add a new custom field" "link"
    And I click on "Programme customfield" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I click on "Save changes" "button" in the "Adding a new Programme customfield" "dialogue"
    Then I should see "Test field"
    And I log out

  Scenario: Delete a custom course select field
    When I click on "Add a new custom field" "link"
    And I click on "Programme customfield" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I click on "Save changes" "button" in the "Adding a new Programme customfield" "dialogue"
    And I wait until the page is ready
    And I click on "Delete" "link" in the "Test field" "table_row"
    And I click on "Yes" "button" in the "Confirm" "dialogue"
    Then I should not see "Test field"
    And I log out
