<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';


const I_RATE = 44100;

$oGenerator = new Signal\Generator\Sine();

$oLFO = new Oscillator\Basic(
    $oGenerator,
    I_RATE,
    5
);

$oModulator = new Oscillator\AM(
    $oGenerator,
    I_RATE,
    880
);

$oCarrier = new Oscillator\FM(
    $oGenerator,
    I_RATE,
    440
);

$oOutput = new Output\Raw16BitLittle;

$oOutput->open('test_simple_fm.bin');

do {
    $oOutput->write(
        $oCarrier->emit(
            $oModulator->emit(
                $oLFO->emit(128)
            )
        )
    );
} while ($oCarrier->getPosition() < I_RATE);


$oOutput->close();
