<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iMaxSamples = 3 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Simple(
    new Signal\Generator\Square(),
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

$oOutput   = new Output\Wav;
$oEnvelope = new Output\Wav;
$oOutput->open('output/test_pitch_output.wav');
$oEnvelope->open('output/test_pitch_envelope.wav');

do {
    $oPitch = $oEnvelopeGenerator->emit();
    $oEnvelope->write($oPitch);
    $oOscillator->setPitchModulation($oPitch);
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iMaxSamples);
$oEnvelope->close();
$oOutput->close();
