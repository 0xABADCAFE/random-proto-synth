<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Generator\IGenerator;
use ABadCafe\Synth\Signal\Packet;

/**
 * Simple Oscillator. Single Generator, continuous output.
 */
class Simple extends Base {

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

    /**
     * @inheritdoc
     */
    public function emitPhaseModulated(Packet $oPhase) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}

