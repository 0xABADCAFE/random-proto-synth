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

namespace ABadCafe\Synth\Signal\Audio\Stream\Filter\Karlsen;

use ABadCafe\Synth\Signal\Audio\Stream\Filter;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

/**
 * Karlsen\BandPass
 */
class BandPass extends Filter\Karlsen {

    /**
     * Specific method for fixed C and fixed Q
     */
    protected function processFixedCFixedQ() {
        $oInputSamples  = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples = $this->oLastOutputPacket->getValues();
        $fResonance     = $this->fFixedResonance  * self::F_SCALE_MAX_Q;
        foreach ($oInputSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fFixedCutoff, $fResonance);
            $oOutputSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * Specific method for varying C and fixed Q
     */
    protected function processVaryingCFixedQ() {
        $oInputSamples  = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples = $this->oLastOutputPacket->getValues();
        $oCutoffValues  = $this->oCutoffControl->emit($this->iLastIndex)->getValues();
        $fResonance     = $this->fFixedResonance  * self::F_SCALE_MAX_Q;
        foreach ($oInputSamples as $i => $fInput) {
            $this->filterSample($fInput, $oCutoffValues[$i], $fResonance);
            $oOutputSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * Specific method for fixed C and varying Q
     */
    protected function processFixedCVaryingQ() {
        $oInputSamples    = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples   = $this->oLastOutputPacket->getValues();
        $oResonanceValues = $this->oResonanceControl->emit($this->iLastIndex)->getValues();
        foreach ($oInputSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fFixedCutoff, self::F_SCALE_MAX_Q * $oResonanceValues[$i]);
            $oOutputSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * Specific method for varying C and varying Q
     */
    protected function processVaryingCVaryingQ() {
        $oInputSamples    = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples   = $this->oLastOutputPacket->getValues();
        $oCutoffValues    = $this->oCutoffControl->emit($this->iLastIndex)->getValues();
        $oResonanceValues = $this->oResonanceControl->emit($this->iLastIndex)->getValues();
        foreach ($oInputSamples as $i => $fInput) {
            $this->filterSample($fInput, $oCutoffValues[$i], self::F_SCALE_MAX_Q * $oResonanceValues[$i]);
            $oOutputSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }
}

