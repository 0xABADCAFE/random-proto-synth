<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOscillator1 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 440);
$oEnvelope    = new Envelope\Generator\DecayPulse(1.1, 0.06);
$oAmplifier   = new Signal\Audio\Stream\Amplifier($oOscillator1, $oEnvelope);
$iOneSecond   = Signal\Context::get()->getProcessRate();
$oOutput      = new Output\Wav;

$oOutput->open('output/test_amplifier.wav');

do {
    $oOutput->write($oAmplifier->emit());
} while ($oAmplifier->getPosition() < $iOneSecond);



