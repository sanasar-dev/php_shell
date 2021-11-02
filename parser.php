#!/usr/bin/env php
<?php
namespace php_shell;

$GLOBALS['config'] = require_once 'config.php';
require_once 'Parser.php';
require_once 'helpers.php';

$start = microtime(true);

// Get arguments
$options = getopt(null, ['file:', 'unique-combinations:']);

// Parse file
(new Parser($options))->parse();

printf(
        "Total time: %s\r\nMemory Used (current): %s\r\nMemory Used (peak): %s", 
        round(microtime(true) - $start, 4), formatBytes(memory_get_usage()), formatBytes(memory_get_peak_usage()) . "\n"
);

?>
