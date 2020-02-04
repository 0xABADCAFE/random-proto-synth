<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iSamples = 5 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Super(
    new Signal\Generator\SawUp(),
    [
        [1.0, 0.5],   // Fundamental harmonic
        [0.995, 0.2], // Detuned fundamental (flat)
        [1.005, 0.2], // Detuned fundamental (sharp)
        [1.997, 0.1], // Detuned octave above (flat)
        [2.003, 0.1], // Detuned octave above (sharp)
        [0.501, 0.1], // Detuned octave below (flat)
        [0.499, 0.1]  // Detuned octave below (sharp)
    ],
    110 // Base frequency (Hz)
);

// Render straight to wav output

echo "Testing : ", $oOscillator, "\n";
$oOutput = new Output\Wav;
$oOutput->open('output/test_super.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iSamples);
echo "End: ", $oOscillator, "\n\n";

$oOutput->close();
