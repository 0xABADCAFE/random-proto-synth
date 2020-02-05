<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iSamples = 5 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Super(
    new Signal\Generator\SawUp(),
    [
        [1.001,     0.25, 0.0],
        [1/1.001,   0.25, 0.25],
        [2.001,     0.25, 0.75],
        [1+1/1.001, 0.25, 1.0],
        [3.001,     0.25, 0.75],
        [2+1/1.001, 0.25, 1.0],
    ],
    55 // Base frequency (Hz)
);

// Render straight to wav output

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCutoffEnvelope = new Oscillator\Simple(
    new Signal\Generator\Sine(0.25, 1.0),
    5
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oResonanceEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.0, [
            [0.9, 2.5],
            [0.0, 2.5]
        ]
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOutput = new Output\Wav;
$oOutput->open('output/test_super2.wav');

$oFilter = new Signal\Filter\ResonantLowPass;

do {
    $oFilter
        ->setCutoffControl($oCutoffEnvelope->emit())
        ->setResonanceControl($oResonanceEnvelope->emit());
    $oOutput->write(
        $oFilter->filter(
            $oOscillator->emit()
        )
    );
} while ($oOscillator->getPosition() < $iSamples);

$oOutput->close();
