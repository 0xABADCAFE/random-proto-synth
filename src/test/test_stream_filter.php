<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOscillator = new Oscillator\Audio\Simple(new Signal\Waveform\Noise(), 110);
$oEnvelope   = new Envelope\Generator\DecayPulse(1.0, 0.05);
$oFilter     = new Signal\Audio\Stream\Filter\Karlsen\BandPass($oOscillator, $oEnvelope);
$iOneSecond  = Signal\Context::get()->getProcessRate();
$oOutput     = new Output\Wav;

$oOutput->open('output/test_filter.wav');

$oFilter->setFixedResonance(0);

do {
    $oOutput->write($oFilter->emit());
} while ($oFilter->getPosition() < $iOneSecond);



