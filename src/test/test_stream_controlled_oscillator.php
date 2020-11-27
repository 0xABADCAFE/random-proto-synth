<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        -12.0, [
            [-12.0, 0.25],
            [12.0, 0.5],
        ]
    )
);
$oOscillator = new Oscillator\Audio\Prototype(
    new Signal\Waveform\Sine(),
    new Signal\Control\Stream\SemitonesToMultiplier($oEnvelope),
    440
);


$iOneSecond   = Signal\Context::get()->getProcessRate();
$oOutput      = new Output\Wav;
$oOutput->open('output/test_stream_controlled_osc.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);



