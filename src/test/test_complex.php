<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        220
    ),

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Start at Zero amplitude
            [
                [1, 4],       // Max  amplitude after +4 seconds
                [0, 4],       // Zero amplitude after +4 secpmds
                [0.5, 6]      // Half amplitude after +6 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Start input pitch
            [
                [-36, 4]       // 3 Octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\Square(),
        440
    ),

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,                // Start at Max amplitude
            [
                [0.5, 5]      // Half amplitude after +5 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Start at input pitch
            [
                [-36, 4]       // 3 Octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier2 = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Simple(
        new Signal\Generator\SawDown(),
        444
    ),

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.25,             // Start at Quarter amplitude
            [
                [0.75, 4]     // Three quarters amplitude after +4 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Start at input pitch
            [
                [-36, 4]       // 3 octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oFilter = new Operator\ControlledFilter(

    // Filter type
    new Signal\Filter\ResonantLowPass(),

    // Cutoff Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.1,              // Start at 10% cutoff
            [
                [1, 1],       // Max cutoff after +1 second
                [0.1, 4],     // 10% cutoff after +4 seconds
                [0.01, 4]     //  1% cutoff after +4 seconds
            ]
        )
    ),

    // Resonance Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Start at zero resonance
            [
                [0.5, 1],     // Half resonance after +1 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Define the Signal Routing
$oCarrier
    ->attachPhaseModulatorInput($oModulator, 2);
$oFilter
    ->attachSignalInput($oCarrier, 1)
    ->attachSignalInput($oCarrier2, 1)
;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Define the final summing output and render
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oFilter, 1.0)
    ->open('output/test_complex.wav')
    ->render(8.0)
    ->close();
;

