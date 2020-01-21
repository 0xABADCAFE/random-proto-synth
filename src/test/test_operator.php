<?php

namespace ABadCafe\Synth;

//include 'profiling.php';

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Operator.php';
require_once '../Output.php';

const I_TIME = 4;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();


// Specify a simple sinewave based operator at 55Hz
$oModulator1 = new Operator\Simple(
    // Oscillator

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        1987
    ),
    // Envelope
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,                // Initial Level
            [
                [ 1/2, 0.5],
                [ 1/4, 0.5],
                [ 1/8, 0.5],
                [ 1/16, 0.5],
                [ 1/32, 0.5],
            ]
        )
    )
);

// Specify a simple sinewave based operator at 3Hz for some vibrato
$oModulator2 = new Operator\Simple(
    // Oscillator

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        3
    ),
    // Envelope
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,                // Initial Level
            []
        )
    )
);

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier = new Operator\Simple(

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        440
    ),
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,              // Initial Level
            [                 // Level / Time Pairs
                [ 1/2, 1],
                [ 1/4, 1],
                [ 1/8, 1],
                [ 1/16, 1],
                [ 0, 1]
            ]
        )
    )
);

// Define the Algorithm...
$oCarrier
    ->attachPhaseModulator($oModulator1, 0.5)
    ->attachPhaseModulator($oModulator2, 0.2)
;

$oOutput = new Output\Wav;
$oOutput->open('output/operator.wav');

$fStart = microtime(true);
do {
    $oOutput->write(
        $oCarrier->emit()
    );
} while ($oCarrier->getPosition() < $iMaxSamples);
$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated ", I_TIME, " seconds in ", $fElapsed, " seconds\n";
