<?php

namespace ABadCafe\Synth;

include 'profiling.php';

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Operator.php';
require_once '../Output.php';

const I_TIME = 4;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();

// Specify a simple sinewave based operator at 55Hz
$oModulator1 = new Operator\ModulatedOscillator(
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
                [ 0, 5]
            ]
        )
    )
);

// Specify a simple sinewave based operator at 3Hz for some vibrato
$oModulator2 = new Operator\ModulatedOscillator(
    // Oscillator

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        3.5
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
$oCarrier1 = new Operator\ModulatedOscillator(

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
    ),

    // Pitch Envelope
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [                 // Level / Time Pairs
                [1, 0.5],
                [0, 0.5]
            ]
        )
    )
);

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier2 = new Operator\ModulatedOscillator(

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        110
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


$oModulator1
    ->attachPhaseModulatorInput($oModulator2, 10);

// Define the Algorithm...
$oCarrier1
    ->attachPhaseModulatorInput($oModulator1, 0.5)
    ->attachPhaseModulatorInput($oModulator2, 0.2)
;

// Define the Algorithm...
$oCarrier2
    ->attachPhaseModulatorInput($oModulator1, 0.2)
    ->attachPhaseModulatorInput($oModulator2, 0.5)
;

// Define the final summing output
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oModulator1, 0.1) // For fun, let's include a 10% mix of the direct output of this operator
    ->attachSignalInput($oCarrier1, 0.65)
    ->attachSignalInput($oCarrier2, 0.25)
;

$oOutput->open('output/operator2.wav');

$fStart = microtime(true);

do {
    $oOutput->emit();
} while ($oOutput->getPosition() < $iMaxSamples);

$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated ", I_TIME, " seconds in ", $fElapsed, " seconds\n";
