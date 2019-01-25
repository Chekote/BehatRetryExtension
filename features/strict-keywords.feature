Feature: Require that Gherkin keyword matches to invoke step definition
  In order to ensure I am using the correct step definition
  As a Behat developer
  The test should fail when I use the wrong Gherkin keyword to invoke a step definition

  Background:
    Given a context file "features/bootstrap/FeatureContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        /**
         * Context for definition keywords.
         */
        class FeatureContext implements Context
        {
            /** @Given the system has some state */
            public function ensureSomething(): void {}

            /** @When I perform some action */
            public function doSomething(): void {}

            /** @Then I assert something */
            public function assertSomething(): void { }
        }
        """

  Scenario: Scenario fails if the wrong keyword is used for "Given" step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            When the system has some state
        """
    When I run Behat
    Then it should fail with "Step 'the system has some state' was matched but the wrong keyword 'When' was used. The step should be invoked with 'Given'"

  Scenario Outline: Scenario fails if "And" or "But" is used to "inherit" the wrong keyword for "Given" step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            When I perform some action
            <keyword> the system has some state
        """
    When I run Behat
    Then it should fail with "Step 'the system has some state' was matched but the wrong keyword 'When' was used. The step should be invoked with 'Given'"

    Examples:
      | keyword |
      | And     |
      | But     |

  Scenario: Scenario fails if wrong keyword is used for "When" step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            Given I perform some action
        """
    When I run Behat
    Then it should fail with "Step 'I perform some action' was matched but the wrong keyword 'Given' was used. The step should be invoked with 'When'"

  Scenario Outline: Scenario fails if "And" is used to "inherit" wrong keyword for When step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            Given the system has some state
            <keyword> I perform some action
        """
    When I run Behat
    Then it should fail with "Step 'I perform some action' was matched but the wrong keyword 'Given' was used. The step should be invoked with 'When'"

    Examples:
      | keyword |
      | And     |
      | But     |

  Scenario: Scenario fails if wrong keyword is used for "Then" step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            When I assert something
        """
    When I run Behat
    Then it should fail with "Step 'I assert something' was matched but the wrong keyword 'When' was used. The step should be invoked with 'Then'"

  Scenario Outline: Scenario fails if "And" or "But" is used to "inherit" wrong keyword for "Then" step when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            When I perform some action
            <keyword> I assert something
        """
    When I run Behat
    Then it should fail with "Step 'I assert something' was matched but the wrong keyword 'When' was used. The step should be invoked with 'Then'"

    Examples:
      | keyword |
      | And     |
      | But     |

  Scenario: Scenario passes if correct keyword is used when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            Given the system has some state
            When I perform some action
            Then I assert something
        """
    When I run Behat
    Then it should pass

  Scenario: Scenario passes if "And" or "But" is used to "inherit" keyword when strict keywords is enabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: true
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            Given the system has some state
              And the system has some state
              But the system has some state
              And the system has some state
              But the system has some state
             When I perform some action
              And I perform some action
              But I perform some action
              And I perform some action
              But I perform some action
             Then I assert something
              And I assert something
              But I assert something
              And I assert something
              But I assert something
        """
    When I run Behat
    Then it should pass

  Scenario: Scenario passes if wrong keyword is used when strict keywords is disabled
    Given a Behat configuration containing:
        """
        default:
          extensions:
            Chekote\BehatRetryExtension:
              strictKeywords: false
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Some Random Feature

          Scenario: Test the feature
            When the system has some state
            Then I perform some action
            Given I assert something
        """
    When I run Behat
    Then it should pass
