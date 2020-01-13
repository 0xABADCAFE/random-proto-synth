<?php

namespace ABadCafe\Synth\Signal;
require_once 'Signal.php';

$oInputPacket = new Packet(range(-5.0, 5.0, 0.25));

echo "Input Packet => ";
print_r($oInputPacket);

$aGenerators = [
    new Generator\DC(0.5),
    new Generator\Sine(),
    new Generator\Square(),
    new Generator\Noise(),
];

foreach ($aGenerators as $oGenerator) {
    echo "Testing ", get_class($oGenerator), ", getPeriod() = ", $oGenerator->getPeriod(), "\nOutput Packet => ";
    $oOutputPacket = $oGenerator->map($oInputPacket);
    print_r($oOutputPacket);
}
