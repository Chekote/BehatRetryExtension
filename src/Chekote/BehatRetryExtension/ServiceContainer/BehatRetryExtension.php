<?php namespace Chekote\BehatRetryExtension\ServiceContainer;

use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Chekote\BehatRetryExtension\Tester\RuntimeStepTester;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extension for automatically retrying "Then" steps.
 */
class BehatRetryExtension implements Extension
{
    /** The service that our step tester needs to replace */
    const SERVICE_ID = 'tester.step.wrapper.hookable.inner';

    const CONFIG_KEY = 'spinner';

    const CONFIG_ENV_TIMEOUT = 'BEHAT_RETRY_TIMEOUT';
    const CONFIG_PARAM_ALL = 'parameters';
    const CONFIG_PARAM_INTERVAL = 'interval';
    const CONFIG_PARAM_TIMEOUT = 'timeout';
    const CONFIG_PARAM_STRICT_KEYWORDS = 'strictKeywords';

    const CONFIG_ALL = self::CONFIG_KEY . '.' . self::CONFIG_PARAM_ALL;
    const CONFIG_RETRY_INTERVAL = self::CONFIG_KEY . '.' . self::CONFIG_PARAM_INTERVAL;
    const CONFIG_TIMEOUT = self::CONFIG_KEY . '.' . self::CONFIG_PARAM_TIMEOUT;
    const CONFIG_STRICT_KEYWORDS = self::CONFIG_KEY . '.' . self::CONFIG_PARAM_STRICT_KEYWORDS;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(RuntimeStepTester::class, [
            new Reference(DefinitionExtension::FINDER_ID),
            new Reference(CallExtension::CALL_CENTER_ID),
        ]);

        $container->setDefinition(self::SERVICE_ID, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::CONFIG_KEY;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        /* @scrutinizer ignore-call Scrutinizer does not understand the context that determines the return types  */
        $builder
            ->children()
                ->floatNode(self::CONFIG_PARAM_TIMEOUT)->defaultValue(5)->end()
                ->integerNode(self::CONFIG_PARAM_INTERVAL)->defaultValue(100000000)->end()
                ->booleanNode(self::CONFIG_PARAM_STRICT_KEYWORDS)->defaultTrue()->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::CONFIG_ALL, $config);
        $envTimeout = $this->getEnvTimeout();
        $container->setParameter(
            self::CONFIG_TIMEOUT,
            $envTimeout !== null ? $envTimeout : $config[self::CONFIG_PARAM_TIMEOUT]
        );
        $container->setParameter(self::CONFIG_RETRY_INTERVAL, $config[self::CONFIG_PARAM_INTERVAL]);
        $container->setParameter(self::CONFIG_STRICT_KEYWORDS, $config[self::CONFIG_PARAM_STRICT_KEYWORDS]);

        $this->loadRuntimeStepTester($container);
    }

    /**
     * Sets up up the RuntimeStepTester.
     *
     * The specified container should have a self::CONFIG_TIMEOUT and a self::CONFIG_RETRY_INTERVAL parameter.
     *
     * @param ContainerBuilder $container the container with the parameters to use.
     */
    private function loadRuntimeStepTester(ContainerBuilder $container)
    {
        RuntimeStepTester::$timeout = $container->getParameter(self::CONFIG_TIMEOUT);
        RuntimeStepTester::$interval = $container->getParameter(self::CONFIG_RETRY_INTERVAL);
        RuntimeStepTester::$strictKeywords = $container->getParameter(self::CONFIG_STRICT_KEYWORDS);
    }

    /**
     * Gets the timeout from the environment variable, if set.
     *
     * @return float|null the timeout in seconds, or null if not set or invalid.
     */
    private function getEnvTimeout(): ?float
    {
        $raw = getenv(self::CONFIG_ENV_TIMEOUT);
        if ($raw === false) {
            return null;
        }

        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            fwrite(STDERR, 'Warning: Environment variable ' . self::CONFIG_ENV_TIMEOUT .
                ' should be numeric (seconds), got "' . $raw . '"' . PHP_EOL);

            return null;
        }

        $numeric = (float) $value;
        if ($numeric < 0) {
            fwrite(STDERR, 'Warning: Environment variable ' . self::CONFIG_ENV_TIMEOUT .
                ' must be >= 0, got "' . $raw . '"' . PHP_EOL);

            return null;
        }

        return $numeric;
    }
}
