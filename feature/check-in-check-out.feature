Feature: People can check in and out of a building

  Scenario: A person can check into a building
    Given the building "CodeId" was registered
    When "Bob" checks into "CodeId"
    Then "Bob" should have been checked into "CodeId"

  Scenario: Multiple check-ins are illegal
    Given the building "CodeId" was registered
    And "Bob" has checked into "CodeId"
    When "Bob" checks into "CodeId"
    Then "Bob" should have been checked into "CodeId"
    And a check-in anomaly was detected
