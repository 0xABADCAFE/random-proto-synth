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
$oModulator1 = new Operator\ModulatedOscillator(
    // Oscillator

    new Oscillator\Simple(
        new Signal\Generator\Sine(),
        55
    ),
    // Envelope
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0,                // Initial Level
            [
                [1, 4],
                [0, 4]
            ]
        )
    )
);

// Specify a carrier operator using a morphing wave oscillator that changes from sine to square, at 220Hz.
$oCarrier = new Operator\ModulatedOscillator(

    new Oscillator\Simple(
        new Signal\Generator\Square(),
        220
    ),
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            1,              // Initial Level
            [                 // Level / Time Pairs
                [0.5, 5]
            ]
        )
    )
);

$oFilter = new Operator\EnvelopedFilter(
    new Signal\Filter\ResonantLowPass(),
    // Envelope
    new Envelope\Generator\LinearInterpolated(
        new Envelope\Shape(
            0.1,// Initial Level
            [
                [0.5, 4],
                [0.1, 4]
            ]
        )
    ),
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
$oCarrier->attachPhaseModulatorInput($oModulator1, 1);
$oFilter->attachSignalInput($oCarrier, 1);

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
