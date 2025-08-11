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


  Scenario: CLI timeout option overrides config file setting
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
    When I run Behat with "--retry-timeout=2"
    Then it should pass
