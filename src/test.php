<?php

namespace ABadCafe\Synth;
require_once 'Signal.php';
require_once 'Oscillator.php';

$aRange = range(-5.0, 5.0, 0.25);

$oInputPacket = new Signal\Packet($aRange);

$aRange = array_map('strval', $aRange);


echo "Input Packet => ";
print_r($oInputPacket);

// Test some generators
$aGenerators = [
    new Signal\Generator\DC(0.5),
    new Signal\Generator\Sine(),
    new Signal\Generator\Square(),
    new Signal\Generator\Saw(),
    new Signal\Generator\Noise(),
];

foreach ($aGenerators as $oGenerator) {
    $oOscillator = new Oscillator\Basic(
        $oGenerator,
        11025,     // Sample rate Hz
        11025/4.0  // Signal Frequency Hz
    );

    echo "Testing : ", $oOscillator, "\n";
    for ($i = 0; $i<4; $i++) {
        $oPacket = $oOscillator->emit(Signal\Packet::I_MIN_LENGTH);
        echo "iteration #", $i, " => ", json_encode($oPacket->getValues(), JSON_PRETTY_PRINT), "\n";
    }

}

