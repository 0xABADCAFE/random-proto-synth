<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';

$iOneSecond = Signal\Context::get()->getProcessRate();
$oGenerator = new Signal\Generator\Sine();

$oLFO = new Oscillator\Basic(
    $oGenerator,
    0.1
);

$oLFO2 = new Oscillator\Basic(
    new Signal\Generator\SawDown(0, 1),
    4
);


$oModulator = new Oscillator\AmplitudeModulated(
    $oGenerator,
    55
);

$oCarrier = new Oscillator\PhaseAndAmplitudeModulated(
    new Signal\Generator\Square(),
    220
);

$oOutput = new Output\Wav;

$oOutput->open('output/test_fm.wav');

$fStart = microtime(true);
do {
    $oOutput->write(
        $oCarrier->emit(
            $oModulator->emit(
                $oLFO->emit()
            ),
            $oLFO2->emit()
        )
    );
} while ($oCarrier->getPosition() < $iOneSecond * 5);
$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated 5 seconds in ", $fElapsed, " seconds\n";
