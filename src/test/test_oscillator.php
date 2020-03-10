<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

// Test some generators
$aGenerators = [
    'sine_full'     => new Signal\Generator\Sine(),
    'sine_pos'      => new Signal\Generator\Sine(0.01, 1.0),
    'sine_neg'      => new Signal\Generator\Sine(-1.0, -0.01),
    'square_full'   => new Signal\Generator\Square(),
    'square_pos'    => new Signal\Generator\Square(0.01, 1.0),
    'square_neg'    => new Signal\Generator\Square(-1.0, -0.01),
    'saw_up_full'   => new Signal\Generator\SawUp(),
    'saw_up_pos'    => new Signal\Generator\SawUp(0.01, 1.0),
    'saw_up_neg'    => new Signal\Generator\SawUp(-1.0, -0.01),
    'saw_down_full' => new Signal\Generator\SawDown(),
    'saw_down_pos'  => new Signal\Generator\SawDown(0.01, 1.0),
    'saw_down_neg'  => new Signal\Generator\SawDown(-1.0, -0.01),
    'triangle_full' => new Signal\Generator\Triangle(),
    'triangle_pos'  => new Signal\Generator\Triangle(0.01, 1.0),
    'triangle_neg'  => new Signal\Generator\Triangle(-1.0, -0.01),
    'noise_full'    => new Signal\Generator\Noise(),
    'noise_pos'     => new Signal\Generator\Noise(0.01, 1.0),
    'noise_neg'     => new Signal\Generator\Noise(-1.0, -0.01),
    'wavetable'     => new Signal\Generator\Wavetable(8)
];

$oTable = $aGenerators['wavetable']->getTable();
$fScale = 1.0 / count($oTable);
foreach($oTable as $i => $fValue) {
    $oTable[$i] = (2 ** ($i * $fScale)) - 1.5;
}

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
// $oOscillator = new Oscillator\Morphing(
//     $aGenerators['sine'],
//     $aGenerators['sine'],
//     $aGenerators['sine'],
//     440,
//     1.5,
//     4
// );
//
// echo "Testing : ", $oOscillator, "\n";
// $oOutput->open('output/test_morph.wav');
//
// do {
//     $oOutput->write($oOscillator->emit());
// } while ($oOscillator->getPosition() < $iOneSecond);
// echo "End: ", $oOscillator, "\n\n";
//
// $oOutput->close();
