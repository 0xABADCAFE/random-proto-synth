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
        // This is a bit messy
        if ($this->oPitchShift) {
            if ($this->oPhaseShift) {
                // Apply Pitch shift and Phase modulation. Note that the Pitch Shift array already factors in fScaleVal
                foreach ($oValues as $i => $fValue) {
                    $oValues[$i] = ($this->oPitchShift[$i] * $this->iSamplePosition++) + $this->oPhaseShift[$i];
                }
            } else {
                // Apply pitch shift only
                foreach ($oValues as $i => $fValue) {
                    $oValues[$i] = ($this->oPitchShift[$i] * $this->iSamplePosition++);
                }
            }
        } else if ($this->oPhaseShift) {
            // Apply Phase modulation
            foreach ($oValues as $i => $fValue) {
                $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + $this->oPhaseShift[$i];

            }
        } else {
            // No modulation at all
            foreach ($oValues as $i => $fValue) {
                $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
            }
        }

        return $this->oGenerator->map($this->oGeneratorInput);
    }

}

