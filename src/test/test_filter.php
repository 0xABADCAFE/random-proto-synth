<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$iMaxSamples = 5 * Signal\Context::get()->getProcessRate();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOscillator = new Oscillator\Morphing(
    new Signal\Generator\Square(),
    new Signal\Generator\SawDown(),
    new Signal\Generator\Sine(-0.5, 0.5),
    55,
    2.001,
    1
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCutoffEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.5, [
            [0.99, 0.05],
            [0.1, 0.2],
                [0.01, 2],
                [1, 1],
                [0.05, 1]
        ]
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oResonanceEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.5, [
            [0.9, 2]
        ]
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOutput = new Output\Play;
$oOutput->open('output/test_filter.wav');

$oFilter = new Signal\Filter\ResonantLowPass;

do {
    $oFilter
        ->setCutoffControl($oCutoffEnvelope->emit())
        ->setResonanceControl($oResonanceEnvelope->emit());
    $oOutput->write(
        $oFilter->filter(
            $oOscillator->emit()
        )
    );
} while ($oOscillator->getPosition() < $iMaxSamples);

$oOutput->close();
