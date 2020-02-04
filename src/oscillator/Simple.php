<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Generator\IGenerator;
use ABadCafe\Synth\Signal\Packet;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Simple Oscillator. Single Generator, continuous output.
 */
class Simple extends Base {

    /**
     * @inheritdoc
     */
    public function emit() : Packet {

        $oValues = $this->oGeneratorInput->getValues();

        if ($this->oPitchShift) {
            // Every sample point has a new frequency, we must also correct the phase for every sample point.
            // The phase correction is accumulated, which is equivalent to integrating over the time step.
            $fTimeStep     = Context::get()->getSamplePeriod() * $this->oGenerator->getPeriod();
            foreach ($this->oPitchShift as $i => $fNextFrequency) {
                $fTime                   = $fTimeStep * $this->iSamplePosition++;
                $oValues[$i]             = ($this->fCurrentFrequency * $fTime) + $this->fPhaseCorrection;
                $this->fPhaseCorrection += $fTime * ($this->fCurrentFrequency - $fNextFrequency);
                $this->fCurrentFrequency = $fNextFrequency;
            }
        } else {
            // Basic linear intervals, there is no phase adjustment
            foreach ($oValues as $i => $fValue) {
                $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
            }
        }

        // Apply phase modulation
        if ($this->oPhaseShift) {
            foreach ($this->oPhaseShift as $i => $fPhase) {
                $oValues[$i] += $fPhase;
            }
        }

        return $this->oGenerator->map($this->oGeneratorInput);
    }

}
