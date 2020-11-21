<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

// Test some generators
$aWaveforms = [
    'sine'     => new Signal\Waveform\Sine(),
    'square'   => new Signal\Waveform\Square(),
    'saw_up'   => new Signal\Waveform\SawUp(),
    'saw_down' => new Signal\Waveform\SawDown(),
    'triangle' => new Signal\Waveform\Triangle()
];

const I_STEPS = 16;

$oInputPacket = new Signal\Control\Packet();
$oInputData   = $oInputPacket->getValues();

foreach ($aWaveforms as $sName => $oWaveform) {

    printf(
        "\nTesting %s [%s], Period %.6f\n",
        $sName,
        get_class($oWaveform),
        $oWaveform->getPeriod()
    );

    $fScale = $oWaveform->getPeriod() / I_STEPS;
    for ($i = 0; $i <= I_STEPS*2; ++$i) {
        $oInputData[$i] = $fScale*($i - I_STEPS);
    }
    $oOutputData = $oWaveform->map($oInputPacket)->getValues();
    for ($i = 0; $i <= I_STEPS*2; ++$i) {
        printf(
            "\t%2d | %+.4f | %+.4f\n",
            $i - I_STEPS,
            $oInputData[$i],
            $oOutputData[$i]
        );
    }
}




