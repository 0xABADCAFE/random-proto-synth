<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier = new Operator\UnmodulatedOscillator(

    // Wave function
    new Oscillator\Super(
        new Signal\Generator\Square(),
        [
            [1.0,  0.5, 0.0],
            [845/587, 0.25, 0.0],
        ]
    ),

    // Frequency ratio
    1.0,

    // Detune
    0.0,

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,                // Initial Level
            [
                [ 1/2, 0.125],
                [ 1/4, 0.125],
                [ 1/8, 0.125],
                [ 1/16, 0.125],
                [ 1/32, 0.125],
                [ 1/64, 0.125],
                [ 1/128, 0.125],
                [ 1/256, 1]
            ]
        )
    )
);

$oFilter = new Operator\ControlledFilter(

    // TODO - bandbass here
    // Filter type
    new Signal\Filter\ResonantLowPass(),

    // Cutoff Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.5,              // Start at 10% cutoff
            [
            ]
        )
    ),

    // Resonance Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.6,              // Start at 10% cutoff
            [
            ]
        )
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oFilter->attachSignalInput($oCarrier, 1.0);
$oCarrier->setNoteName('D5');



// Define the final summing output and render
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oFilter, 1.0)
    ->open('output/cowbell.wav')
    ->render(2.0)
    ->close();
