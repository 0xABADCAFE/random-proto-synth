<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

const I_TIME = 1;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();

// Create a sine wave to use as the Pan value
$oOscillator = new Oscillator\Simple(
    new Signal\Generator\Sine(),
    110
);

// Test the linear pan law
$oPanLaw = new Signal\PanLaw\Linear;

// Render to a Wav so the data can be inspected
$oOutput = $oOutput = new Output\Wav(
    Output\Wav::I_DEF_RATE_SIGNAL_DEFAULT,
    Output\Wav::I_DEF_RESOLUTION_BITS,
    Signal\IChannelMode::I_CHAN_STEREO
);
$oOutput->open('output/test_pan_law.wav');

// Render a second of stereo. We expect that:
// 1. The output signal is always zero or positive for both the left and right channel (ie biased 50%)
// 2. The output signal starts at 50% in each channel
// 3. The left and right sine waves are completely out of phase.
do {
    $oOutput->write(
        $oPanLaw->map($oOscillator->emit())
    );
} while ($oOscillator->getPosition() < $iMaxSamples);

$oOutput->close();
