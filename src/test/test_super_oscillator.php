<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

// Test some generators
$aWaveforms = [
    'sine_full'     => new Signal\Waveform\Sine(),
    'square_full'   => new Signal\Waveform\Square(),
    'saw_up_full'   => new Signal\Waveform\SawUp(),
    'saw_down_full' => new Signal\Waveform\SawDown(),
    'triangle_full' => new Signal\Waveform\Triangle(),
    'table'         => new Signal\Waveform\Table(8)
];

$oTable = $aWaveforms['table']->getValues();
$fScale = 1.0 / count($oTable);
foreach($oTable as $i => $fValue) {
    $oTable[$i] = (2 ** ($i * $fScale)) - 1.5;
}

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Play;

$aHarmonics = [
    [1.0,   0.5, 0], // Fundamental harmonic
    [0.995, 0.2, 0], // Detuned fundamental (flat)
    [1.005, 0.2, 0], // Detuned fundamental (sharp)
    [1.997, 0.1, 0], // Detuned octave above (flat)
    [2.003, 0.1, 0], // Detuned octave above (sharp)
    [0.501, 0.1, 0], // Detuned octave below (flat)
    [0.499, 0.1, 0]  // Detuned octave below (sharp)
];

foreach ($aWaveforms as $sName => $oWaveform) {
    $oOscillator = new Oscillator\Audio\Super(
        $oWaveform,
        $aHarmonics,
        110
    );

    echo "Testing : ", $oOscillator, "\n";
    $oOutput->open('output/test_super_' . $sName . ".wav");

    do {
        $oOutput->write($oOscillator->emit());
    } while ($oOscillator->getPosition() < $iOneSecond);
    echo "End: ", $oOscillator, "\n\n";

    $oOutput->close();
}

