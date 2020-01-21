<?php

namespace ABadCafe\Synth;

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Output.php';

const I_TIME = 8;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();

$oGenerator = new Signal\Generator\Sine();

$oLFO = new Oscillator\Simple(
    $oGenerator,
    0.1
);

$oModulator = new Oscillator\Simple(
    $oGenerator,
    55
);

$oModulatorShape = new Envelope\Shape;
$oModulatorShape
    ->initial(0)
    ->append(0.75, 0.3)
    ->append(1.0,  0.2)
    ->append(0.5, 0.5)
    ->append(10, 3)
    ->append(0, 4);
$oModulatorEnvelope = new Envelope\Generator\LinearInterpolated($oModulatorShape);

$oCarrier = new Oscillator\Simple(
    new Signal\Generator\Square(),
    220
);

$oCarrierShape = new Envelope\Shape;
$oCarrierShape
    ->initial(0.5)            // Initial Level
    ->append(1, 0.01)
    ->append(0.75, 2)
    ->append(0.5, 5);

$oCarrierEnvelope = new Envelope\Generator\LinearInterpolated($oCarrierShape);

$oOutput = new Output\Wav;
$oOutput->open('output/test_fm.wav');

$fStart = microtime(true);
do {
    $oOutput->write(
        $oCarrier->emitPhaseModulated(
            $oModulator->emitPhaseModulated(
                $oLFO->emit()
            )->modulateWith(
                $oModulatorEnvelope->emit()
            )
        )->modulateWith(
            $oCarrierEnvelope->emit()
        )
    );
} while ($oCarrier->getPosition() < $iMaxSamples);
$fElapsed = microtime(true) - $fStart;

$oOutput->close();

echo "Generated ", I_TIME, " seconds in ", $fElapsed, " seconds\n";
