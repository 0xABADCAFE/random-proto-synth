<?php

namespace ABadCafe\Synth\Signal;

require_once '../Synth.php';

$foo = new Audio\Packet;
$bar = new Control\Packet;

$foo->fillWith(1.0)->sumWith($foo);

$bar->fillWith(0.5);

print_r($foo);

try {
    $bar->sumWith($foo);
} catch (\Throwable $oError) {
    echo "Caught ", $oError, "\n";
}
