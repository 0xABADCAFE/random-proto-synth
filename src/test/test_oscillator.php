<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

// Test some generators
$aGenerators = [
    'flat'     => new Signal\Generator\Flat(0.5),
    'sine'     => new Signal\Generator\Sine(),
    'square'   => new Signal\Generator\Square(),
    'saw_up'   => new Signal\Generator\SawUp(),
    'saw_down' => new Signal\Generator\SawDown(),
    'triangle' => new Signal\Generator\Triangle(),
    'noise'    => new Signal\Generator\Noise()
];

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

foreach ($aGenerators as $sName => $oGenerator) {
    $oOscillator = new Oscillator\Simple(
        $oGenerator,
        440
    );

    echo "Testing : ", $oOscillator, "\n";
    $oOutput->open('output/test_' . $sName . ".wav");

    do {
        $oOutput->write($oOscillator->emit());
    } while ($oOscillator->getPosition() < $iOneSecond);
    echo "End: ", $oOscillator, "\n\n";

    $oOutput->close();
}

// Quick test of a morphing oscillator
$oOscillator = new Oscillator\Morphing(
    $aGenerators['sine'],
    $aGenerators['sine'],
    $aGenerators['sine'],
    440,
    1.5,
    4
);

echo "Testing : ", $oOscillator, "\n";
$oOutput->open('output/test_morph.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);
echo "End: ", $oOscillator, "\n\n";

$oOutput->close();
