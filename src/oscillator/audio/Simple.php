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
 * Simple Oscillator. Single Waveform, continuous output.
 */
class Simple extends Base {

    /**
     * @inheritDoc
     */
    protected function emitNew() : Signal\Audio\Packet {
        $oInputValues = $this->oWaveformInput->getValues();
        if ($this->oPitchModulator) {
            // We have something modulating our basic pitch. This gets complicated...
            $oPitchShift = $this->oPitchModulator->emit($this->iLastIndex)->getValues();

            // Every sample point has a new frequency, but we can't just use the instantaneous Waveform value for
            // that as it would be the value that the function has if it was always at that frequency.
            // Therefore we must also correct the phase for every sample point too. The phase correction is
            // accumulated, which is equivalent to integrating over the time step.
            foreach ($oPitchShift as $i => $fNextFrequencyMultiplier) {
                $fNextFrequency          = $this->fFrequency * $fNextFrequencyMultiplier;
                $fTime                   = $this->fTimeStep * $this->iSamplePosition++;
                $oInputValues[$i]        = ($this->fCurrentFrequency * $fTime) + $this->fPhaseCorrection;
                $this->fPhaseCorrection += $fTime * ($this->fCurrentFrequency - $fNextFrequency);
                $this->fCurrentFrequency = $fNextFrequency;
            }
        } else {
            // Basic linear intervals, there is no phase adjustment for changes in pitch. Nice and simple.
            foreach ($oInputValues as $i => $fValue) {
                $oInputValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
            }
        }

        if ($this->oPhaseModulator) {
            // We have somthing modulating our basic phase. Thankfully this is just additive. We assume the
            // phase modulation is normalised, such that 1.0 is a complete full cycle of our waveform.
            // We simply multiply the shift by our Waveform's period value to get this.
            $oPhaseShift = $this->oPhaseModulator->emit($this->iLastIndex)->getValues();
            foreach ($oPhaseShift as $i => $fValue) {
                $oInputValues[$i] += $this->fWaveformPeriod * $fValue;
            }
        }
        return $this->oLastOutput = $this->oWaveform->map($this->oWaveformInput);
    }
}
