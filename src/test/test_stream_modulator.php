<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';


$oOscillator1 = new Oscillator\Audio\Simple(new Signal\Waveform\Sine(), 440);
$oOscillator2 = new Oscillator\Audio\Simple(new Signal\Waveform\SawDown(0.0, 1.0), 880);

$oMixer = new Signal\Audio\Stream\Modulator($oOscillator1, $oOscillator2);


$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

$oOutput->open('output/test_stream_modulator.wav');

do {
    $oOutput->write($oMixer->emit());
} while ($oMixer->getPosition() < $iOneSecond);



