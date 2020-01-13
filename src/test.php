<?php

namespace ABadCafe\Synth\Signal;
require_once 'Signal.php';

$aRange = range(-5.0, 5.0, 0.25);

$oInputPacket = new Packet($aRange);

$aRange = array_map('strval', $aRange);


echo "Input Packet => ";
print_r($oInputPacket);

// Test some generators
$aGenerators = [
    new Generator\DC(0.5),
    new Generator\Sine(),
    new Generator\Square(),
    new Generator\Saw(),
    new Generator\Noise(),
];

foreach ($aGenerators as $oGenerator) {
    echo "Testing ", get_class($oGenerator), ", getPeriod() = ", $oGenerator->getPeriod(), "\nOutput Packet => ";
    $oOutputPacket = $oGenerator->map($oInputPacket);

    echo json_encode(
        array_combine(
            $aRange,
            $oOutputPacket->getValues()
        ),
        JSON_PRETTY_PRINT
    ), "\n";
}

