<?php

/**
 * In the absence of an autoloader, we use a simple C-like header. Each top level include pulls in whatever is defined
 * within it's corresponding subdirectory.
 */
namespace ABadCafe\Synth;

require_once 'Utility.php';
require_once 'Map.php';
require_once 'Signal.php';
require_once 'Envelope.php';
require_once 'Oscillator.php';
require_once 'Output.php';
require_once 'Operator.php';
