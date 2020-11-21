<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

const I_SECONDS    = 6;
const I_NUM_OSC    = 1024;
const F_MAX_OUTPUT = 16.0/I_NUM_OSC;

$oWaveform = new Signal\Waveform\SawDown(-F_MAX_OUTPUT, F_MAX_OUTPUT);

$aOscillators    = [];
$aPitchEnvelopes = [];
for ($i = 0; $i < I_NUM_OSC; ++$i) {
    $aOscillators[$i] = new Oscillator\Audio\Simple(
        $oWaveform,
        440.0
    );

    $fDetune     = 0.00025 * mt_rand(-1000, 1000);
    $fStartRatio = 0.1 * mt_rand(-600, -360);
    $fEndRatio   = 12*mt_rand(-2, 2);

    $aEnvelopes[$i] = new Envelope\Waveform\LinearInterpolated(
        new Envelope\Shape(
            $fStartRatio + $fDetune, [
                [$fEndRatio + $fDetune, 4.0],
                [$fEndRatio + $fDetune/4, 1.0]
            ]
        )
    );
}

$oOutput   = new Output\Wav;
$oOutput->open('output/test_kibbidrone.wav');

$iMaxSamples = I_SECONDS * Signal\Context::get()->getProcessRate();
$oMixed = new Signal\Audio\Packet();
do {
    $oMixed->fillWith(0.0);
    foreach ($aEnvelopes as $i => $oEnvelope) {
        $oPitch = $oEnvelope->emit();
        $aOscillators[$i]->setPitchModulation($oPitch);
        $oMixed->sumWith($aOscillators[$i]->emit());
    }
    $oOutput->write($oMixed);
} while ($aOscillators[0]->getPosition() < $iMaxSamples);

$oOutput->close();
