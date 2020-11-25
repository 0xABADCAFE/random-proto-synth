<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';


$oOscillator1 = new Oscillator\Audio\Simple(new Signal\Waveform\Sine(), 440);
$oOscillator2 = new Oscillator\Audio\Simple(new Signal\Waveform\Triangle(), 880);
$oOscillator3 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 440);

$oMixer = new Signal\Audio\Stream\FixedMixer();
$oMixer
    ->addStream($oOscillator2, -0.25)
    ->addStream($oOscillator1, 0.75)
    ->addStream($oOscillator2, 0.25)
    ->addStream($oOscillator3, 0.25);


$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

$oOutput->open('output/test_fixed_mixer.wav');

do {
    $oOutput->write($oMixer->emit());
} while ($oMixer->getPosition() < $iOneSecond);



