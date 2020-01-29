<?php

namespace ABadCafe\Synth;

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';


// Test some generators
$aGenerators = [
    'sine'     => new Signal\Generator\Sine(),
    'square'   => new Signal\Generator\Square(),
    'saw_up'   => new Signal\Generator\SawUp(),
    'saw_down' => new Signal\Generator\SawDown(),
    'triangle' => new Signal\Generator\Triangle()
];

const I_STEPS = 16;

$oInputPacket = new Signal\Packet();
$oInputData   = $oInputPacket->getValues();

foreach ($aGenerators as $sName => $oGenerator) {

    printf(
        "\nTesting %s [%s], Period %.6f\n",
        $sName,
        get_class($oGenerator),
        $oGenerator->getPeriod()
    );

    $fScale = $oGenerator->getPeriod() / I_STEPS;
    for ($i = 0; $i <= I_STEPS*2; ++$i) {
        $oInputData[$i] = $fScale*($i - I_STEPS);
    }
    $oOutputData = $oGenerator->map($oInputPacket)->getValues();
    for ($i = 0; $i <= I_STEPS*2; ++$i) {
        printf(
            "\t%2d | %+.4f | %+.4f\n",
            $i - I_STEPS,
            $oInputData[$i],
            $oOutputData[$i]
        );
    }
}




