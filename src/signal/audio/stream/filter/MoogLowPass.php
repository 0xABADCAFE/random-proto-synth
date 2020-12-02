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

namespace ABadCafe\Synth\Signal\Audio\Stream\Filter;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * MoogLowPass
 *
 * Resonant Low Pass filter modelled on the Moog Voltage Controlled Filter
 * Based on https://www.musicdsp.org/en/latest/Filters/26-moog-vcf-variation-2.html
 */
class MoogLowPass extends Base {

    const
        F_SCALE_MAX_Q  = 4.0,
        F_TAP_SCALE    = 0.3,
        F_COEFF_1      = 1.16,
        F_COEFF_2      = 0.15,
        F_COEFF_3      = 0.35013
    ;

    /**
     * Filter memory
     */
    private float
        $fIn1  = 0.0, $fIn2  = 0.0, $fIn3  = 0.0, $fIn4  = 0.0,
        $fOut1 = 0.0, $fOut2 = 0.0, $fOut3 = 0.0, $fOut4 = 0.0
    ;

    /**
     * @inheritdoc
     */
    public function reset() : self {
        parent::reset();
        $this->fIn1  = 0.0;
        $this->fIn2  = 0.0;
        $this->fIn3  = 0.0;
        $this->fIn4  = 0.0;
        $this->fOut1 = 0.0;
        $this->fOut2 = 0.0;
        $this->fOut3 = 0.0;
        $this->fOut4 = 0.0;
        return $this;
    }

    /**
     * Specific method for fixed C and fixed Q.
     */
    protected function processFixedCFixedQ() {
        $oInputSamples  = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples = $this->oLastOutputPacket->getValues();
        $f1             = $this->fFixedCutoff    * self::F_COEFF_1;
        $f2             = $this->fFixedResonance * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $f1 * $f1);
        $f3             = 1.0 - $f1;
        $f1             = self::F_COEFF_3 * $f1 * $f1 * $f1 * $f1;
        foreach ($oInputSamples as $i => $fNextInput) {
            $fNextInput        -= $this->fOut4 * $f2;
            $fNextInput        *= $f1;
            $this->fOut1        = $fNextInput  + self::F_TAP_SCALE * $this->fIn1 + $f3 * $this->fOut1; // Pole 1
            $this->fIn1         = $fNextInput;
            $this->fOut2        = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $f3 * $this->fOut2; // Pole 2
            $this->fIn2         = $this->fOut1;
            $this->fOut3        = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $f3 * $this->fOut3; // Pole 3
            $this->fIn3         = $this->fOut2;
            $this->fOut4        = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $f3 * $this->fOut4; // Pole 4
            $this->fIn4         = $this->fOut3;
            $oOutputSamples[$i] = $this->fOut4;
        }
    }

    /**
     * Specific method for varying C and fixed Q
     */
    protected function processVaryingCFixedQ() {
        $oInputSamples  = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples = $this->oLastOutputPacket->getValues();
        $oCutoffValues  = $this->oCutoffControl->emit($this->iLastIndex)->getValues();
        $fResonance     = $this->fFixedResonance * self::F_SCALE_MAX_Q;
        foreach ($oInputSamples as $i => $fNextInput) {
            $f1                 = $oCutoffValues[$i] * self::F_COEFF_1;
            $f2                 = $fResonance * (1.0 - self::F_COEFF_2 * $f1 * $f1);
            $f3                 = 1.0 - $f1;
            $fNextInput        -= $this->fOut4 * $f2;
            $fNextInput        *= self::F_COEFF_3 * ($f1 * $f1 * $f1 * $f1);
            $this->fOut1        = $fNextInput  + self::F_TAP_SCALE * $this->fIn1 + $f3 * $this->fOut1; // Pole 1
            $this->fIn1         = $fNextInput;
            $this->fOut2        = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $f3 * $this->fOut2; // Pole 2
            $this->fIn2         = $this->fOut1;
            $this->fOut3        = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $f3 * $this->fOut3; // Pole 3
            $this->fIn3         = $this->fOut2;
            $this->fOut4        = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $f3 * $this->fOut4; // Pole 4
            $this->fIn4         = $this->fOut3;
            $oOutputSamples[$i] = $this->fOut4;
        }
    }

    /**
     * Specific method for fixed C and varying Q
     */
    protected function processFixedCVaryingQ() {
        $oInputSamples    = $this->oInputStream->emit($this->iLastIndex)->getValues();
        $oOutputSamples   = $this->oLastOutputPacket->getValues();
        $oResonanceValues = $this->oResonanceControl->emit($this->iLastIndex)->getValues();
        $f1               = $this->fFixedCutoff * self::F_COEFF_1;
        $f3               = 1.0 - $f1;
        foreach ($oInputSamples as $i => $fNextInput) {
            $f2                 = $oResonanceValues[$i] * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $f1 * $f1);
            $fNextInput        -= $this->fOut4 * $f2;
            $fNextInput        *= self::F_COEFF_3 * ($f1 * $f1 * $f1 * $f1);
            $this->fOut1        = $fNextInput  + self::F_TAP_SCALE * $this->fIn1 + $f3 * $this->fOut1; // Pole 1
            $this->fIn1         = $fNextInput;
            $this->fOut2        = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $f3 * $this->fOut2; // Pole 2
            $this->fIn2         = $this->fOut1;
            $this->fOut3        = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $f3 * $this->fOut3; // Pole 3
            $this->fIn3         = $this->fOut2;
            $this->fOut4        = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $f3 * $this->fOut4; // Pole 4
            $this->fIn4         = $this->fOut3;
            $oOutputSamples[$i] = $this->fOut4;
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

        foreach ($oInputSamples as $i => $fNextInput) {
            $f1                 = $oCutoffValues[$i]    * self::F_COEFF_1;
            $f2                 = $oResonanceValues[$i] * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $f1 * $f1);
            $f3                 = 1.0 - $f1;
            $fNextInput        -= $this->fOut4 * $f2;
            $fNextInput        *= self::F_COEFF_3 * ($f1 * $f1 * $f1 * $f1);
            $this->fOut1        = $fNextInput      + self::F_TAP_SCALE * $this->fIn1 + $f3 * $this->fOut1; // Pole 1
            $this->fIn1         = $fNextInput;
            $this->fOut2        = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $f3 * $this->fOut2;  // Pole 2
            $this->fIn2         = $this->fOut1;
            $this->fOut3        = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $f3 * $this->fOut3;  // Pole 3
            $this->fIn3         = $this->fOut2;
            $this->fOut4        = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $f3 * $this->fOut4;  // Pole 4
            $this->fIn4         = $this->fOut3;
            $oOutputSamples[$i] = $this->fOut4;
        }
    }
}
