<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use features\contexts\TestContext;

/**
 * Context for setting the working directory for TestContext's Behat.
 */
class FeatureContext implements Context
{
    /** @var TestContext */
    protected $testContext;

    /**
     * Gathers other Contexts from the Environment.
     *
     * @param  BeforeScenarioScope $scope
     * @throws RuntimeException    If the current environment is not initialized.
     * @return void
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $env = $scope->getEnvironment();

        if (!($env instanceof InitializedContextEnvironment)) {
            throw new RuntimeException(
                'Expected Environment to be ' . InitializedContextEnvironment::class . ', but got ' . get_class($env)
            );
        }

        if (!$this->testContext = $env->getContext(TestContext::class)) {
            throw new RuntimeException('Failed to gather TestContext');
        }
    }

    /**
     * Sets the working directory for tbe TestContext's Behat.
     *
     * @Given the Behat working dir is :dir
     * @param string $dir the directory to use.
     */
    public function setBehatWorkingDir(string $dir): void
    {
        $dirIterator = new RecursiveDirectoryIterator(__DIR__ . '/../../' . $dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if (!$item->isDir()) {
                $this->testContext->thereIsFile($iterator->getSubPathName(), file_get_contents((string) $item));
            }
        }
    }
}
