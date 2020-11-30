<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOscillator = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 110);
$oEnvelope   = new Envelope\Generator\DecayPulse(1.0, 0.1);
$oFilter     = new Signal\Audio\Stream\Filter\MoogLowPass($oOscillator, $oEnvelope);
$iOneSecond  = Signal\Context::get()->getProcessRate();
$oOutput     = new Output\Wav;

$oOutput->open('output/test_filter.wav');

$oFilter->setFixedResonance(0.75);

do {
    $oOutput->write($oFilter->emit());
} while ($oFilter->getPosition() < $iOneSecond);



