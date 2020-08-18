<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iMaxSamples = 3 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Audio\Simple(
    new Signal\Generator\Sine(),
    220
);

$oLFORateControl = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0, [
            [0.0, 0.5],
            [5, 0.5],
            [10.0, 0.5],
        ]
    )
);

$oLFODepthControl = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0, [
            [0.0, 0.5],
            [1.0, 0.5],
            [0.2, 1.0],
        ]
    )
);

$oControlledLFO = new Oscillator\Control\ControlledLFO(
    new Signal\Generator\Sine(),
    $oLFORateControl,
    $oLFODepthControl
);

$oOutput   = new Output\Play;
$oOutput->open('output/test_pitch_output.wav');


do {
    $oPitch = $oControlledLFO->emit();
    $oOscillator->setPitchModulation($oPitch);
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iMaxSamples);

$oOutput->close();
