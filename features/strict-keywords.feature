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

  Scenario: Scenario fails if wrong keyword is used when strict keywords is enabled
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
            And I assert something
        """
    When I run Behat
    Then it should fail with "Step 'I assert something' was matched but the wrong keyword 'When' was used. The step should be invoked with 'Then'"

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
            Given the system has some state
            When I perform some action
            And I assert something
        """
    When I run Behat
    Then it should pass
