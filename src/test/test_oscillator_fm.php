<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';

$iOneSecond = Signal\Context::get()->getProcessRate();
$oGenerator = new Signal\Generator\Sine();

$oLFO = new Oscillator\Basic(
    $oGenerator,
    5
);

$oModulator = new Oscillator\AM(
    $oGenerator,
    880
);

$oCarrier = new Oscillator\FM(
    $oGenerator,
    440
);

$oOutput = new Output\Raw16BitLittle;

$oOutput->open('test_simple_fm.bin');

do {
    $oOutput->write(
        $oCarrier->emit(
            $oModulator->emit(
                $oLFO->emit()
            )
        )
    );
} while ($oCarrier->getPosition() < $iOneSecond);


$oOutput->close();
