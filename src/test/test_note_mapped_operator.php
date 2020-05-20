<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOperator = new Operator\UnmodulatedOscillator(
    // Wave function
    new Oscillator\Audio\Simple(
        new Signal\Generator\Sine(),
        440
    ),

    // Amplitude Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,              // Initial Level
            [
                [ 1/2, 1],
            ]
        )
    ),

    // Pitch Control
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [
                [1/12, 2],
                [0, 2]
            ]
        )
    )
);

$oOperator->setNoteName('C3');
