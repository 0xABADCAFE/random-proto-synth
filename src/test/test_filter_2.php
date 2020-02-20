<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

const I_DURATION = 5;

$iMaxSamples = I_DURATION * Signal\Context::get()->getProcessRate();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOscillator = new Oscillator\Simple(
    new Signal\Generator\Noise()
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oCutoffEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        1.0, [
            [0.001, I_DURATION],
        ]
    )
);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$oOutput = new Output\Play;
$oOutput->open('output/test_filter_2.wav');

$oFilter = new Signal\Filter\ResonantLowPass;

do {
    $oFilter
        ->setCutoffControl($oCutoffEnvelope->emit());
    $oOutput->write(
        $oFilter->filter(
            $oOscillator->emit()
        )
    );
} while ($oOscillator->getPosition() < $iMaxSamples);

$oOutput->close();
