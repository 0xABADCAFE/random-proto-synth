<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator1 = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        1987
    ),

    // Amplitude Control
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator2 = new Operator\FixedOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        3
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        440
    ),

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,              // Initial Level
            [               // Level / Time Pairs
                [ 1/2, 1],
                [ 1/4, 1],
                [ 1/8, 1],
                [ 1/16, 1],
                [ 0, 1]
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Define the Algorithm...
$oCarrier
    ->attachPhaseModulatorInput($oModulator1, 0.5)
    ->attachPhaseModulatorInput($oModulator2, 0.2)
;

// Define the final summing output and render
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oCarrier, 1.0)
    ->open('output/operator.wav')
    ->render(4.0)
    ->close();
