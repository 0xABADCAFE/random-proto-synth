<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOscillator = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 220);
$oEnvelope   = new Envelope\Generator\DecayPulse(1.1, 0.06);
$oFilter     = new Signal\Audio\Stream\Filter\MoogLowPass($oOscillator);
$iOneSecond  = Signal\Context::get()->getProcessRate();
$oOutput     = new Output\Wav;

$oOutput->open('output/test_filter.wav');

do {
    $oOutput->write($oFilter->emit());
} while ($oFilter->getPosition() < $iOneSecond);



