<?php namespace Chekote\BehatRetryExtension\Context;

use Behat\Behat\Context\Context;
use Chekote\BehatRetryExtension\Tester\RuntimeStepTester;

/**
 * Context for working with auto-retrying assertions (Then steps)
 */
class BehatRetryContext implements Context
{
    /** @var float the timeout setting for RuntimeStepTester before a Scenario is ran */
    protected $originalTimeout;

    /** @var int the interval setting for RuntimeStepTester before a Scenario is ran */
    protected $originalInterval;

    /**
     * Sets the number of seconds that an assertion (Then step) should retry before failing.
     *
     * @Given assertions will retry for :timeout seconds before failing
     * @param float $timeout the number of seconds
     */
    public function setTimeout($timeout) {
        RuntimeStepTester::$timeout = $timeout;
    }

    /**
     * Sets the number of nanoseconds that should pass between each retry of an assertion (Then step).
     *
     * @Given assertions will retry every :interval nanoseconds
     * @param int $interval the number of nanoseconds
     */
    public function setInterval($interval) {
        RuntimeStepTester::$interval = $interval;
    }

    /**
     * Records the configuration of the RuntimeStepTester.
     *
     * The config is recorded so that it can be modified after the Scenario has ran, thus ensuring that config settings
     * changed during the Scenario are reset back to their original settings.
     *
     * @BeforeScenario
     */
    public function recordConfig() {
        $this->originalTimeout = RuntimeStepTester::$timeout;
        $this->originalInterval = RuntimeStepTester::$interval;
    }

    /**
     * Restores the RuntimeStepTester config to it's original state.
     *
     * The config is restored to it's state before the Scenario was ran.
     *
     * @AfterScenario
     */
    public function restoreConfig() {
        $this->setTimeout($this->originalTimeout);
        $this->setInterval($this->originalInterval);
    }
}
