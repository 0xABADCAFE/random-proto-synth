<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;

/**
 * Basic
 */
class Basic extends Base implements IOutputOnly {

    /**
     * @inheritdoc
     */
    public function emit() : Packet {
        $oValues = $this->oGeneratorInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}

/**
 * Amplitude modulated
 */
class AM extends Base implements ISingleInput {

    public function emit(Packet $oAmplitudeModulator) : Packet {
        $oValues = $this->oGeneratorInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map($this->oGeneratorInput)->modulateWith($oAmplitudeModulator);
    }
}

/**
 * Phase modulated
 */
class FM extends Base implements ISingleInput {

    public function emit(Packet $oPhaseModulator) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhaseModulator->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}
