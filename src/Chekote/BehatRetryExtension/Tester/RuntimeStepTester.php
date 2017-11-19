<?php namespace Chekote\BehatRetryExtension\Tester;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\FailedStepSearchResult;
use Behat\Behat\Tester\Result\SkippedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Tester executing step tests in the runtime.
 *
 * This class is a copy of \Behat\Behat\Tester\Runtime\RuntimeStepTester by Konstantin Kudryashov <ever.zet@gmail.com>.
 * It has a modified testDefinition() method to implement the retry functionality.
 *
 * I would ideally like to extend or wrap the existing RuntimeStepTester, but neither is possible because the class
 * is final, and the method is private. v_v
 */
final class RuntimeStepTester implements StepTester
{
    /** Number of seconds to attempt "Then" steps before accepting a failure */
    public static $timeout;

    /** @var int number of nanoseconds to wait between each retry of "Then" steps */
    public static $interval;

    /** @var array list of Gherkin keywords */
    protected static $keywords = ['Given', 'When', 'Then'];

    /**
     * @var DefinitionFinder
     */
    private $definitionFinder;

    /**
     * @var CallCenter
     */
    private $callCenter;

    /** @var string The last "Given", "When", or "Then" keyword encountered */
    protected $lastKeyword;

    /**
     * Initialize tester.
     *
     * @param DefinitionFinder $definitionFinder
     * @param CallCenter       $callCenter
     */
    public function __construct(DefinitionFinder $definitionFinder, CallCenter $callCenter)
    {
        $this->definitionFinder = $definitionFinder;
        $this->callCenter = $callCenter;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip = false)
    {
        $this->updateLastKeyword($step);

        try {
            $search = $this->searchDefinition($env, $feature, $step);
            $result = $this->testDefinition($env, $feature, $step, $search, $skip);
        } catch (SearchException $exception) {
            $result = new FailedStepSearchResult($exception);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        return new SuccessfulTeardown();
    }

    /**
     * Searches for a definition.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return SearchResult
     */
    private function searchDefinition(Environment $env, FeatureNode $feature, StepNode $step)
    {
        return $this->definitionFinder->findDefinition($env, $feature, $step);
    }

    /**
     * Tests found definition.
     *
     * @param Environment  $env
     * @param FeatureNode  $feature
     * @param StepNode     $step
     * @param SearchResult $search
     * @param bool         $skip
     *
     * @return StepResult
     */
    private function testDefinition(Environment $env, FeatureNode $feature, StepNode $step, SearchResult $search, $skip)
    {
        if (!$search->hasMatch()) {
            return new UndefinedStepResult();
        }

        if ($skip) {
            return new SkippedStepResult($search);
        }

        $call = $this->createDefinitionCall($env, $feature, $search, $step);

        $lambda = function () use ($call) {
            return $this->callCenter->makeCall($call);
        };

        // @todo We can only "spin" if we are interacting with a remote browser. If the browser is
        // running in the same thread as this test (such as with Goutte or Zombie), then spinning
        // will only prevent that process from continuing, and the test will either pass immediately,
        // or not at all. We need to find out how to check what Driver we're using...

        // if we're in a Then (assertion) block, and self::$timeout is not zero, we need to spin
        $result = $this->lastKeyword == 'Then' && self::$timeout ? $this->spin($lambda) : $lambda();

        return new ExecutedStepResult($search, $result);
    }

    /**
     * Records the keyword for the step.
     *
     * This allows us to know where we are when processing And or But steps.
     *
     * @param  StepNode $step
     * @return void
     */
    protected function updateLastKeyword(StepNode $step)
    {
        $keyword = $step->getKeyword();
        if (in_array($keyword, self::$keywords)) {
            $this->lastKeyword = $keyword;
        }
    }

    /**
     * Continually calls an assertion until it passes or the timeout is reached.
     *
     * @param  callable   $lambda The lambda assertion to call. Must take no arguments and return
     *                            a CallResult.
     * @return CallResult
     */
    protected function spin(callable $lambda)
    {
        $start = microtime(true);

        $result = null;
        while (microtime(true) - $start < self::$timeout) {
            /** @var $result CallResult */
            $result = $lambda();

            if (!$result->hasException() || ($result->getException() instanceof PendingException)) {
                break;
            }

            time_nanosleep(0, self::$interval);
        }

        return $result;
    }

    /**
     * Creates definition call.
     *
     * @param Environment  $env
     * @param FeatureNode  $feature
     * @param SearchResult $search
     * @param StepNode     $step
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(Environment $env, FeatureNode $feature, SearchResult $search, StepNode $step)
    {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall($env, $feature, $step, $definition, $arguments);
    }
}
