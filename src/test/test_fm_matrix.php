<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

const
    I_TIME    = 5,
    F_CARRIER = 220
;

$iMaxSamples = I_TIME * Signal\Context::get()->getProcessRate();

$aWaveforms = [
    'sine'     => new Signal\Waveform\Sine(),
    'square'   => new Signal\Waveform\Square(),
    'saw_up'   => new Signal\Waveform\SawUp(),
    'saw_down' => new Signal\Waveform\SawDown(),
    'triangle' => new Signal\Waveform\Triangle(),
];

$oModulatorEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
        0.0,                // Start at Zero amplitude
        [
            [0, 0.25],      // Hold for 0.25
            [1, 1.5],       // Rise to 1 over 1.5
            [1, 0.25],      // Hold for 0.25
            [2, 1.5],       // Rise to 2 over 1.5
            [2, 0.25],      // Hold for 0.25
            [0, 1.25]       // Drop to zero over 1.25
        ]
    )
);

$aRatios = [
    'half'   => 0.5,
    'unison' => 1.0,
    'fifth'  => 1.5,
    'double' => 2.0
];

$oOutput = new Output\Play;

foreach ($aWaveforms as $sCarrierName => $oCarrierWaveform) {
    echo "Testing Carrier : ", $sCarrierName, "\n";

    foreach ($aWaveforms as $sModulatorName => $oModulatorWaveform) {
        foreach ($aRatios as $sRatioName => $fRatio) {

            echo "\tModulator : ", $sModulatorName, " @ ", $fRatio, " x Carrier\n";

            $oModulator = new Oscillator\Audio\Simple(
                $oModulatorWaveform,
                F_CARRIER * $fRatio
            );
            $oModulatorEnvelope->reset();

            $oCarrier = new Oscillator\Audio\Simple(
                $oCarrierWaveform,
                F_CARRIER
            );

            $oOutput->open(sprintf(
                'output/fm/carrier_%s_modulator_%s_%s.wav',
                $sCarrierName,
                $sModulatorName,
                $sRatioName
            ));

            do {
                $oCarrier->setPhaseModulation(
                    $oModulator
                        ->emit()
                        ->levelControl(
                            $oModulatorEnvelope->emit()
                        )
                );

                $oOutput->write(
                    $oCarrier->emit()
                );
            } while ($oModulator->getPosition() < $iMaxSamples);

            $oOutput->close();
        }
    }

}


