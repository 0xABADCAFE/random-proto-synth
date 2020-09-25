<?php

/**
 *      _/_/_/  _/_/_/  _/      _/  _/_/_/      _/_/_/
 *   _/          _/    _/_/  _/_/  _/    _/  _/
 *  _/  _/_/    _/    _/  _/  _/  _/_/_/      _/_/
 * _/    _/    _/    _/      _/  _/              _/
 *  _/_/_/  _/_/_/  _/      _/  _/        _/_/_/
 *
 *  - Grossly Impractical Modular PHP Synthesiser -
 *
 */

declare(strict_types = 1);

namespace ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Simple Oscillator. Single Generator, continuous output.
 */
class Simple extends Base {

    use Signal\TContextIndexAware;

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Audio\Packet {

        if ($this->useLast($iIndex)) {
            return $this->oLastOutput;
        }

        $oValues = $this->oGeneratorInput->getValues();

        if ($this->oPitchShift) {
            // Every sample point has a new frequency, we must also correct the phase for every sample point.
            // The phase correction is accumulated, which is equivalent to integrating over the time step.
            $fTimeStep     = Signal\Context::get()->getSamplePeriod() * $this->oGenerator->getPeriod();
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

        $this->oLastOutput = $this->oGenerator->map($this->oGeneratorInput);

        //$this->postProcess();

        return $this->oLastOutput;
    }

}
