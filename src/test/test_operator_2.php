<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator1 = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine()
    ),

    // Frequency ratio
    1987.0/440.0,

    // Detune
    0.0,

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
                [ 0, 5]
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator2 = new Operator\UnmodulatedOscillator(
    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        3.5
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier1 = new Operator\ModulatableOscillator(
    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine()
    ),

    // Frequency Ratio
    1.0,

    // Detune
    0.0,

    // Amplitude Control
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

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [                 // Level / Time Pairs
                [1/12, 2],
                [0, 2]
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier2 = new Operator\ModulatableOscillator(

    new Oscillator\Simple(
        new Signal\Generator\Sine()
    ),

    // Frequency Ratio
    0.25,

    // Detune
    1.0,

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator1
    ->attachPhaseModulatorInput($oModulator2, 1);

// Define the Algorithm...
$oCarrier1
    ->attachPhaseModulatorInput($oModulator1, 0.5)
    ->attachPhaseModulatorInput($oModulator2, 0.1)
;

// Define the Algorithm...
$oCarrier2
    ->attachPhaseModulatorInput($oModulator1, 0.2)
    ->attachPhaseModulatorInput($oModulator2, 0.5)
;

$oModulator1->setNoteName('C4');
$oCarrier1->setNoteName('C4');
$oCarrier2->setNoteName('C4');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Define the final summing output
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oModulator1, 0.1) // For fun, let's include a 10% mix of the direct output of this operator
    ->attachSignalInput($oCarrier1, 0.65)
    ->attachSignalInput($oCarrier2, 0.25)
    ->open('output/operator2.wav')
    ->render(5.0)
    ->close();
;
