<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oWaveform = new Signal\Waveform\Sine();
$oWaveform->setShaper(new Signal\Waveform\Shaper\FixedPhaseFeedback(1.0));

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

$oOscillator = new Oscillator\Audio\Simple(
    $oWaveform,
    440
);

echo "Testing : ", $oOscillator, "\n";
$oOutput->open('output/test_wave_shaper.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);
echo "End: ", $oOscillator, "\n\n";

$oOutput->close();
