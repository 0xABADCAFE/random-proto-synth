<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';


// Test some generators
$aGenerators = [
    'dc'     => new Signal\Generator\DC(0.5),
    'sine'   => new Signal\Generator\Sine(),
    'square' => new Signal\Generator\Square(),
    'saw'    => new Signal\Generator\Saw(),
    'noise'  => new Signal\Generator\Noise(),
];

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Raw16BitLittle;

foreach ($aGenerators as $sName => $oGenerator) {
    $oOscillator = new Oscillator\Basic(
        $oGenerator,
        440
    );

    echo "Testing : ", $oOscillator, "\n";
    $oOutput->open('test_' . $sName . ".bin");

    do {
        $oOutput->write($oOscillator->emit());
    } while ($oOscillator->getPosition() < $iOneSecond);
    echo "End: ", $oOscillator, "\n\n";

    $oOutput->close();
}

