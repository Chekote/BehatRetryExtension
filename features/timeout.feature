Feature: Only fail "Then" steps after timeout has expired
  In order to have asynchronous assertions pass
  As a Behat developer
  I want to have "Then" assertions retry for a specific period of time

  Background:
    Given the Behat working dir is "fixtures"

  Scenario: Assertion fails if timeout is too short
    Given a Behat configuration containing:
        """
        default:
          suites:
            default:
              contexts:
                - FeatureContext
          extensions:
            Chekote\BehatRetryExtension:
              timeout: 0
        """
    When I run Behat
    Then it should fail with:
        """
        File contents are "some text" but "something else" expected.
        """

  Scenario: Assertion succeeds when timeout is long enough
    Given a Behat configuration containing:
        """
        default:
          suites:
            default:
              contexts:
                - FeatureContext
          extensions:
            Chekote\BehatRetryExtension:
              timeout: 2
        """
    When I run Behat
    Then it should pass

  Scenario: Environment variable should override configuration when higher
    Given a Behat configuration containing:
        """
        default:
          suites:
            default:
              contexts:
                - FeatureContext
          extensions:
            Chekote\BehatRetryExtension:
              timeout: 0
        """
    And the BEHAT_RETRY_TIMEOUT environment variable is set to "2"
    When I run Behat
    Then it should pass

  Scenario: Environment variable should override configuration when lower
    Given a Behat configuration containing:
        """
        default:
          suites:
            default:
              contexts:
                - FeatureContext
          extensions:
            Chekote\BehatRetryExtension:
              timeout: 2
        """
    And the BEHAT_RETRY_TIMEOUT environment variable is set to "0"
    When I run Behat
    Then it should fail with:
        """
        File contents are "some text" but "something else" expected.
        """

    Scenario: Should validate BEHAT_RETRY_TIMEOUT environment variable is numeric
      Given a Behat configuration containing:
          """
          default:
            suites:
              default:
                contexts:
                  - FeatureContext
            extensions:
              Chekote\BehatRetryExtension:
                timeout: 0
          """
      And the BEHAT_RETRY_TIMEOUT environment variable is set to "ABC"
      When I run Behat
      Then it should fail with:
          """
          Warning: Environment variable BEHAT_RETRY_TIMEOUT should be numeric (seconds), got "ABC"
          """

    Scenario: Should validate BEHAT_RETRY_TIMEOUT environment variable is not negative
      Given a Behat configuration containing:
          """
          default:
            suites:
              default:
                contexts:
                  - FeatureContext
            extensions:
              Chekote\BehatRetryExtension:
                timeout: 0
          """
      And the BEHAT_RETRY_TIMEOUT environment variable is set to "-1"
      When I run Behat
      Then it should fail with:
          """
          Warning: Environment variable BEHAT_RETRY_TIMEOUT must be >= 0, got "-1"
          """
