<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

const I_TIME = 1;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();

$oPanEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        -1.0, [
            [1.0, I_TIME]
        ]
    )
);

// Test the linear pan law
$oPanLaw = new Signal\PanLaw\CentreMax;

// Render to a Wav so the data can be inspected
$oOutput = $oOutput = new Output\Wav(
    Output\Wav::I_DEF_RATE_SIGNAL_DEFAULT,
    Output\Wav::I_DEF_RESOLUTION_BITS,
    Signal\IChannelMode::I_CHAN_STEREO
);
$oOutput->open('output/test_pan_law.wav');

do {
    $oOutput->write(
        $oPanLaw->map($oPanEnvelope->emit())
    );
} while ($oPanEnvelope ->getPosition() < $iMaxSamples);

$oOutput->close();
