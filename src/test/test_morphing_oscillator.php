<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOscillator  = new Oscillator\Audio\Morphing(
    new Signal\Waveform\Sine(),
    new Signal\Waveform\Triangle(),
    new Signal\Waveform\Sine(),
    220.0,
    1.0,
    2.0
);

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Play;
$oOutput->open('output/test_morphing_oscillator.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);



