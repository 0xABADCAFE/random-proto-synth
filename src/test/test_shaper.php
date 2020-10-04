<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oGenerator = new Signal\Generator\Sine();
$oGenerator->setWaveShaper(new Signal\Generator\WaveShaper\FixedPhaseFeedback(1.0));

$iOneSecond = Signal\Context::get()->getProcessRate();
$oOutput    = new Output\Wav;

$oOscillator = new Oscillator\Audio\Simple(
    $oGenerator,
    440
);

echo "Testing : ", $oOscillator, "\n";
$oOutput->open('output/test_wave_shaper.wav');

do {
    $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);
echo "End: ", $oOscillator, "\n\n";

$oOutput->close();
