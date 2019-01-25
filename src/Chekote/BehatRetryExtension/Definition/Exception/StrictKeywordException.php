<?php namespace Chekote\BehatRetryExtension\Definition\Exception;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Exception\SearchException;
use RuntimeException;

/**
 * Represents an exception caused by an incorrect keyword used to invoke a step definition.
 */
final class StrictKeywordException extends RuntimeException implements SearchException
{
    /**
     * Initializes strict keyword exception.
     *
     * @param string     $keyword    the keyword that was used to invoke the step.
     * @param Definition $definition the definition that matched the step.
     */
    public function __construct($keyword, Definition $definition)
    {
        parent::__construct(
            sprintf(
                "Step '%s' was matched but the wrong keyword '%s' was used. The step should be invoked with '%s'",
                $definition->getPattern(),
                $keyword,
                $definition->getType()
            )
        );
    }
}
