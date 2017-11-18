<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Context for asynchronous file writes.
 */
class FeatureContext implements Context
{
    /** @var string */
    private static $workingDir;

    /** @var Filesystem */
    protected static $filesystem;

    /**
     * Initializes the working directory and filesystem.
     *
     * @BeforeFeature
     */
    public static function beforeFeature()
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
    }

    /**
     * Ensures that the working directory exists and has the correct permissions.
     *
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        self::$filesystem->mkdir(self::$workingDir, 0777);
    }

    /**
     * Removes the working directory.
     *
     * @AfterScenario
     */
    public function afterScenario()
    {
        self::$filesystem->remove(self::$workingDir);
    }

    /**
     * Writes content to a file immediately (non blocking).
     *
     * @Given the file :path contents are :content
     * @param string $file the path to the file to write.
     * @param string $content the content to write to the file.
     */
    public function writeFile(string $file, string $content)
    {
        file_put_contents($this->getFilePath($file), $content);
    }

    /**
     * Writes content to a file after a given time (non blocking).
     *
     * @Given the file :path contents will be :content in :timeout seconds
     * @param string $file the path to the file to write.
     * @param string $content the content to write to the file.
     * @param int $seconds the number of seconds to wait before writing to the file.
     */
    public function writeFileDelayed(string $file, string $content, int $seconds = 0)
    {
        if (!$script = realpath(__DIR__ . '/../../bin/writeFileDelayed.php')) {
            throw new RuntimeException('Cannot find writeFileDelayed.php!');
        }

        $command = sprintf(
            'php %s "%s" %s %s > /dev/null 2>/dev/null &',
            $script,
            $this->getFilePath($file),
            $seconds,
            base64_encode($content)
        );

        exec($command, $output, $exitCode);

        if ($exitCode != 0) {
            throw new RuntimeException("Command '$command' failed with output:\n\n" . implode("\n", $output));
        }
    }

    /**
     * Asserts that the specified file has the specified contents.
     *
     * @Then the file :file contents should be :content
     * @param string $file the path to the file to check.
     * @param string $content the content to check for.
     */
    public function assertFileContents(string $file, string $content)
    {
        $path = $this->getFilePath($file);
        $actual = file_exists($path) ? file_get_contents($path) : '';
        if ($content != $actual) {
            throw new RuntimeException("File contents are \"$actual\" but \"$content\" expected.");
        }
    }

    /**
     * Provides the full path to a file within the working directory.
     *
     * @param  string $file the path of the file
     * @return string the full path
     */
    protected function getFilePath(string $file): string
    {
        return self::$workingDir . $file;
    }
}
