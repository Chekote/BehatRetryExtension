<?php namespace Chekote\BehatRetryExtension\ServiceContainer;

use Behat\Testwork\Cli\Controller;
use Chekote\BehatRetryExtension\Tester\RuntimeStepTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class RetryCliController implements Controller
{

    /**
     * {@inheritDoc}
     */
    public function configure(Command $def): void
    {
        $def->addOption(
            'retry-timeout',
            null,
            InputOption::VALUE_REQUIRED,
            'Override Behat retry timeout (seconds)'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $in, OutputInterface $out)
    {
        $val = $in->getOption('retry-timeout');
        if ($val !== null && $val !== '') {
            RuntimeStepTester::$timeout = (float) $val;
        }
        return null;
    }
}
