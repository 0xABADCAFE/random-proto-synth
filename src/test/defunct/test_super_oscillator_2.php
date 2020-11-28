<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iSamples = 4 * Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Audio\Super(
    new Signal\Waveform\SawDown(),
    [
        [1.001,     0.25, 0.0],
        [1/1.001,   0.25, 0.25],
        [2.001,     0.25, 0.75],
        [1+1/1.001, 0.25, 1.0],
        [3.001,     0.25, 0.75],
        [2+1/1.001, 0.25, 1.0],
    ],
    55 // Base frequency (Hz)
);

// Render straight to wav output

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCutoffEnvelope = new Oscillator\Control\FixedLFO(
    new Signal\Waveform\SawDown(0.05, 1.0),
    8,
    1
);

$oVolumeEnvelope = new Oscillator\Control\FixedLFO(
    new Signal\Waveform\SawDown(0.5, 0.75),
    8,
    1
);


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oResonanceEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.0, [
            [0.5, 2],
            [0.0, 2]
        ]
    )
);

// Very poor man's arp
$oPitchEnvelope = new Signal\Control\Stream\SemitonesToMultiplier(new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.0, [
            [0.0, 0.249],
            [12.0, 0.001],
            [12.0, 0.249],
            [0.0, 0.001],
            [0.0, 0.249],
            [12.0, 0.001],
            [12.0, 0.249],
            [-9.0, 0.001],
            [-9.0, 0.249],
            [3.0, 0.001],
            [3.0, 0.249],
            [-9.0, 0.001],
            [-9.0, 0.249],
            [3.0, 0.001],
            [3.0, 0.249],
            [-7.0, 0.001],
            [-7.0, 0.249],
            [5.0, 0.001],
            [5.0, 0.249],
            [-7.0, 0.001],
            [-7.0, 0.249],
            [5.0, 0.001],
            [5.0, 0.249],
            [-12.0, 0.001],
            [-12.0, 0.249],
            [0.0, 0.001],
            [0.0, 0.249],
            [-12.0, 0.001],
            [-12.0, 0.249],
            [0.0, 0.001],
            [0.0, 0.249],
            [0.0, 0.001],
        ]
    )
));

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOutput = new Output\Play;
$oOutput->open('output/test_super2.wav');

$oFilter = new Signal\Audio\Filter\ResonantLowPass;

do {
    $oOscillator
        ->setPitchModulation($oPitchEnvelope->emit());
    $oFilter
        ->setCutoffControl($oCutoffEnvelope->emit())
        ->setResonanceControl($oResonanceEnvelope->emit());
    $oOutput->write(
        $oFilter->filter(
            $oOscillator->emit()
        )->levelControl($oVolumeEnvelope->emit())
    );
} while ($oOscillator->getPosition() < $iSamples);

$oOutput->close();
