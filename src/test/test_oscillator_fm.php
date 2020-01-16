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

$oModulator = new Oscillator\AM(
    $oGenerator,
    55
);

$oCarrier = new Oscillator\FM(
    new Signal\Generator\Square(),
    220
);

$oOutput = new Output\Wav;

$oOutput->open('test_simple_fm.wav');

$fStart = microtime(true);
do {
    $oOutput->write(
        $oCarrier->emit(
            $oModulator->emit(
                $oLFO->emit()
            )
        )
    );
} while ($oCarrier->getPosition() < $iOneSecond);
$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated 1 second in ", $fElapsed, " seconds\n";
