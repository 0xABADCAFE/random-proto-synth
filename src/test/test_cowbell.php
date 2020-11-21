<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCarrier = new Operator\UnmodulatedOscillator(

    // Wave function
    new Oscillator\Audio\Super(
        new Signal\Waveform\Square(),
        [
            [1.0,  0.5, 0.2],
            [840/587, 0.25, 0.0],
        ]
    ),

    // Frequency ratio
    1.0,

    // Detune
    0.0,

    // Amplitude Control
    new Envelope\Generator\DecayPulse(1.1, 0.06)
);

$oFilter = new Operator\ControlledFilter(

    // TODO - bandbass here
    // Filter type
    new Signal\Audio\Filter\KarlsenBandPass(0.08, 0.25)
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$sNote = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'D5';

$oFilter->attachSignalInput($oCarrier, 1.0);
$oCarrier->setNoteName($sNote);



// Define the final summing output and render
$oOutput = new Operator\PCMOutput(new Output\Play);
$oOutput
    ->attachSignalInput($oFilter, 1.0)
    ->open('output/cowbell.wav')
    ->render(2.0)
    ->close();
