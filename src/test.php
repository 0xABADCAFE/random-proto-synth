<?php

namespace ABadCafe\Synth;
require_once 'Signal.php';
require_once 'Oscillator.php';
require_once 'Output.php';


// Test some generators
$aGenerators = [
    'dc'     => new Signal\Generator\DC(0.5),
    'sine'   => new Signal\Generator\Sine(),
    'square' => new Signal\Generator\Square(),
    'saw'    => new Signal\Generator\Saw(),
    'noise'  => new Signal\Generator\Noise(),
];

$oOutput = new Output\Raw16BitLittle;

const I_RATE = 44100;

foreach ($aGenerators as $sName => $oGenerator) {
    $oOscillator = new Oscillator\Basic(
        $oGenerator,
        I_RATE,     // Sample rate Hz
        440         // Signal Frequency Hz
    );

    echo "Testing : ", $oOscillator, "\n";
    $oOutput->open('test_' . $sName . ".bin");

    do {
        $oOutput->write($oOscillator->emit(128));
    } while ($oOscillator->getPosition() < I_RATE);
    echo "End: ", $oOscillator, "\n\n";

    $oOutput->close();
}

