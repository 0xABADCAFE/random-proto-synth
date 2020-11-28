<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oModulator = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Audio\Simple(
        new Signal\Waveform\Sine()
    ),

    // Frequency Ratio
    0.5,

    // Detune
    0.0,

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.0,                // Start at Zero amplitude
            [
                [1.0, 4.0],       // Max  amplitude after +4 seconds
                [0.0, 4.0],       // Zero amplitude after +4 secpmds
                [0.5, 6.0]      // Half amplitude after +6 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.0,                // Start input pitch
            [
                [-36.0, 4.0]       // 3 Octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Audio\Simple(
        new Signal\Waveform\Square()
    ),

    // Frequency ratio
    1.0,

    // Detune
    0.0,

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1.0,                // Start at Max amplitude
            [
                [0.5, 5.0]      // Half amplitude after +5 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.0,                // Start at input pitch
            [
                [-36.0, 4.0]       // 3 Octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier2 = new Operator\ModulatableOscillator(

    // Wave function
    new Oscillator\Audio\Simple(
        new Signal\Waveform\SawDown()
    ),

    // Frequency ratio
    444.0/440.0,

    // Detune
    0.0,

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.25,             // Start at Quarter amplitude
            [
                [0.75, 4.0]     // Three quarters amplitude after +4 seconds
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.0,                // Start at input pitch
            [
                [-36.0, 4.0]       // 3 octaves down after +4 seconds
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oFilter = new Operator\ControlledFilter(

    // Filter type
    new Signal\Audio\Filter\ResonantLowPass(),

    // Cutoff Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.1,              // Start at 10% cutoff
            [
                [1.0,  1.0],       // Max cutoff after +1 second
                [0.1,  4.0],     // 10% cutoff after +4 seconds
                [0.01, 4.0]     //  1% cutoff after +4 seconds
            ]
        )
    ),

    // Resonance Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.0,                // Start at zero resonance
            [
                [0.5, 1.0],     // Half resonance after +1 seconds
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

$sNote = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'A4';

$oModulator->setNoteName($sNote);
$oCarrier->setNoteName($sNote);
$oCarrier2->setNoteName($sNote);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Define the final summing output and render
$oOutput = new Operator\PCMOutput(new Output\Play);
$oOutput
    ->attachSignalInput($oFilter, 1.0)
    ->open('output/test_complex.wav')
    ->render(8.0)
    ->close();
;

