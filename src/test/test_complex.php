<?php

namespace ABadCafe\Synth;

//include 'profiling.php';

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Operator.php';
require_once '../Output.php';

const I_TIME = 4;

$iMaxSamples = (I_TIME * 2) * Signal\Context::get()->getProcessRate();


// Specify a simple sinewave based operator at 55Hz
$oModulator1 = new Operator\ModulatedOscillator(
    // Oscillator

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        220
    ),
    // Amplitude
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Initial Level
            [
                [1, 4],
                [0, 4],
                [0.5, 6]
            ]
        )
    ),

    // Pitch
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [                 // Level / Time Pairs
                [-3, I_TIME]
            ]
        )
    )
);

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier = new Operator\ModulatedOscillator(

    new Oscillator\Simple(
        new Signal\Generator\Square(),
        440
    ),
    // Amplitude
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,              // Initial Level
            [                 // Level / Time Pairs
                [0.5, 5]
            ]
        )
    ),
    // Pitch
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [                 // Level / Time Pairs
                [-3, I_TIME]
            ]
        )
    )
);

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier2 = new Operator\ModulatedOscillator(

    new Oscillator\Simple(
        new Signal\Generator\SawDown(),
        444
    ),
    // Amplitude
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.25,              // Initial Level
            [                 // Level / Time Pairs
                [0.75, I_TIME]
            ]
        )
    ),
    // Pitch
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,              // Initial Level
            [                 // Level / Time Pairs
                [-3, I_TIME]
            ]
        )
    )
);

$oFilter = new Operator\EnvelopedFilter(
    new Signal\Filter\ResonantLowPass(),
    // Cutoff
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.1,// Initial Level
            [
                [1, 1],
                [0.1, 4],
                [0.01, 4]
            ]
        )
    ),
    // Resonance
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,// Initial Level
            [
                [0.5, 1],
            ]
        )
    )
);

// Define the Algorithm...
$oCarrier->attachPhaseModulatorInput($oModulator1, 2);
$oFilter
    ->attachSignalInput($oCarrier, 1)
    ->attachSignalInput($oCarrier2, 1);

// Define the final summing output
$oOutput = new Operator\PCMOutput(new Output\Wav);
$oOutput
    ->attachSignalInput($oFilter, 1.0)
    ->open('output/test_complex.wav')
;

$fStart = microtime(true);
do {
    $oOutput->emit();
} while ($oFilter->getPosition() < $iMaxSamples);
$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated ", I_TIME, " seconds in ", $fElapsed, " seconds\n";
