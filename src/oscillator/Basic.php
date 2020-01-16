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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * AmplitudeModulated
 */
class AmplitudeModulated extends Base implements ISingleInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oAmplitude) : Packet {
        $oValues = $this->oGeneratorInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map($this->oGeneratorInput)->modulateWith($oAmplitude);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PhaseModulated
 */
class PhaseModulated extends Base implements ISingleInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oPhase) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PhaseAndAmplitudeModulated
 */
class PhaseAndAmplitudeModulated extends Base implements IDualInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oPhase, Packet $oAmplitude) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput)->modulateWith($oAmplitude);
    }
}
