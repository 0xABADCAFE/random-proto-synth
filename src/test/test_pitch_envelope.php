<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iMaxSamples = 3 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Audio\Simple(
    new Signal\Generator\Sine(),
    220
);

$oEnvelopeGenerator = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0, [
            [0.0, 0.5],
            [-1.0, 0.5],
            [-1.0, 0.5],
            [0.0, 0.5]
        ]
    )
);

$oOutput   = new Output\Play;
$oOutput->open('output/test_pitch_output.wav');


do {
    $oPitch = $oEnvelopeGenerator->emit();
    $oOscillator->setPitchModulation($oPitch);
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iMaxSamples);
$oEnvelope->close();
$oOutput->close();
