<?php

declare(strict_types=1);

if (count($argv) != 4) {
    echo "Usage: writeFileDelayed.php FILE DELAY CONTENT\n";
    exit(1);
}

/** @var string $file the file to write to */
$file = $argv[1];

/** @var int $delay the number of seconds to wait before writing to the file */
$delay = (int) $argv[2];

/** @var string $content the content to write to the file */
$content = $argv[3];

sleep($delay);

file_put_contents($file, base64_decode($content));
