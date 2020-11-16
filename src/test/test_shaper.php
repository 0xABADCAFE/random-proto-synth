<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Test some generators
$aGenerators = [
    'sine'     => new Signal\Generator\Sine(-0.9, 0.9),
    'square'   => new Signal\Generator\Square(-0.9, 0.9),
    'saw_up'   => new Signal\Generator\SawUp(-0.9, 0.9),
    'saw_down' => new Signal\Generator\SawDown(-0.9, 0.9),
    'triangle' => new Signal\Generator\Triangle(-0.9, 0.9),
];

$aShapers = [
    'vanilla'    => false,
    'phase_0.25' => new Signal\Generator\WaveShaper\FixedPhaseFeedback(0.25),
    'phase_0.50' => new Signal\Generator\WaveShaper\FixedPhaseFeedback(0.5),
    'phase_0.75' => new Signal\Generator\WaveShaper\FixedPhaseFeedback(0.75),
    'phase_1.00' => new Signal\Generator\WaveShaper\FixedPhaseFeedback(1.0),
    'cap_0.25'   => new Signal\Generator\WaveShaper\FixedCapacitance(0.25),
    'cap_0.50'   => new Signal\Generator\WaveShaper\FixedCapacitance(0.5),
    'cap_0.95'   => new Signal\Generator\WaveShaper\FixedCapacitance(0.95),
];

$iLength = (int)(Signal\Context::get()->getProcessRate() / 8);

foreach ($aGenerators as $sGeneratorName => $oGenerator) {
    foreach ($aShapers as $sShaperName => $oShaper) {
        if ($oShaper) {
            $oGenerator->setWaveShaper($oShaper);
        }

        $sFileName = sprintf(
            'output/waveshaper/test_%s_%s.wav',
            $sGeneratorName,
            $sShaperName
        );

        $oOutput     = new Output\Wav;
        $oOscillator = new Oscillator\Audio\Simple(
            $oGenerator,
            220
        );

        echo "Testing : ", $oOscillator, "\n";
        $oOutput->open($sFileName);

        do {
            $oOutput->write($oOscillator->emit());
        } while ($oOscillator->getPosition() < $iLength);
        echo "End: ", $oOscillator, "\n\n";

        $oOutput->close();

    }
}


