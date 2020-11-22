<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

// Test some generators
$aWaveforms = [
    'sine_full'     => new Signal\Waveform\Sine(),
    'sine_pos'      => new Signal\Waveform\Sine(0.01, 1.0),
    'sine_neg'      => new Signal\Waveform\Sine(-1.0, -0.01),
    'square_full'   => new Signal\Waveform\Square(),
    'square_pos'    => new Signal\Waveform\Square(0.01, 1.0),
    'square_neg'    => new Signal\Waveform\Square(-1.0, -0.01),
    'saw_up_full'   => new Signal\Waveform\SawUp(),
    'saw_up_pos'    => new Signal\Waveform\SawUp(0.01, 1.0),
    'saw_up_neg'    => new Signal\Waveform\SawUp(-1.0, -0.01),
    'saw_down_full' => new Signal\Waveform\SawDown(),
    'saw_down_pos'  => new Signal\Waveform\SawDown(0.01, 1.0),
    'saw_down_neg'  => new Signal\Waveform\SawDown(-1.0, -0.01),
    'triangle_full' => new Signal\Waveform\Triangle(),
    'triangle_pos'  => new Signal\Waveform\Triangle(0.01, 1.0),
    'triangle_neg'  => new Signal\Waveform\Triangle(-1.0, -0.01),
    'noise_full'    => new Signal\Waveform\Noise(),
    'noise_pos'     => new Signal\Waveform\Noise(0.01, 1.0),
    'noise_neg'     => new Signal\Waveform\Noise(-1.0, -0.01),
    'table'         => new Signal\Waveform\Wavetable(8)
];

$oTable = $aWaveforms['table']->getValues();
$fScale = 1.0 / count($oTable);
foreach($oTable as $i => $fValue) {
    $oTable[$i] = (2 ** ($i * $fScale)) - 1.5;
}

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Play;

foreach ($aWaveforms as $sName => $oWaveform) {
    $oOscillator = new Oscillator\Audio\Simple(
        $oWaveform,
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
//     $aWaveforms['sine'],
//     $aWaveforms['sine'],
//     $aWaveforms['sine'],
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
