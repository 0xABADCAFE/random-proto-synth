<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';


$oOscillator1 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 220);
$oOscillator2 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 440);
$oOscillator3 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(), 880);

$oMixer = new Signal\Audio\Stream\Mixer\Fixed();
$oMixer
    ->addStream('osc1', $oOscillator1, 0.5)
    ->addStream('osc2', $oOscillator2, 0.25)
    ->addStream('osc3', $oOscillator3, 0.125);


$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

$oOutput->open('output/test_fixed_mixer.wav');

do {
    $oOutput->write($oMixer->emit());
} while ($oMixer->getPosition() < $iOneSecond);



