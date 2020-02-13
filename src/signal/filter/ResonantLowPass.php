<?php

namespace ABadCafe\Synth\Signal\Filter;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IFilter;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Resonant Low Pass filter
 *
 * Based on https://www.musicdsp.org/en/latest/Filters/26-moog-vcf-variation-2.html
 */
class ResonantLowPass extends Resonant {

    const
        F_SCALE_MAX_Q  = 4.0,
        F_TAP_SCALE    = 0.3,
        F_COEFF_1      = 1.16,
        F_COEFF_2      = 0.15,
        F_COEFF_3      = 0.35013
    ;

    private

        $fIn1,  $fIn2,  $fIn3,  $fIn4,
        $fOut1, $fOut2, $fOut3, $fOut4
    ;

    public function __construct() {
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function reset() : IFilter {
        parent::reset();
        $this->fIn1  = 0;
        $this->fIn2  = 0;
        $this->fIn3  = 0;
        $this->fIn4  = 0;
        $this->fOut1 = 0;
        $this->fOut2 = 0;
        $this->fOut3 = 0;
        $this->fOut4 = 0;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function filter(Packet $oInput) : Packet {
        $oOutput    = clone $oInput;
        $oSamples   = $oOutput->getValues();
        $iCase      = ($this->oCutoff ? 1 : 0) | ($this->oResonance ? 2 : 0);
        switch ($iCase) {
            case 1:  $this->filterVaryingF($oSamples);  break;
            case 2:  $this->filterVaryingQ($oSamples);  break;
            case 3:  $this->filterVaryingFQ($oSamples); break;
            default: $this->filterFlat($oSamples);      break;
        }
        return $oOutput;
    }

    /**
     * filter for fixed F/Q
     */
    protected function filterFlat(SPLFixedArray $oSamples) {
        $fa  = $this->fCutoff    * self::F_COEFF_1;
        $fb  = $this->fResonance * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $fa * $fa);
        $fc  = 1.0 - $fa;
        $fa  = self::F_COEFF_3 * $fa * $fa * $fa * $fa;
        foreach ($oSamples as $i => $fInput) {
            $fInput      -= $this->fOut4 * $fb;
            $fInput      *= $fa;
            $this->fOut1  = $fInput + self::F_TAP_SCALE * $this->fIn1 + $fc * $this->fOut1; // Pole 1
            $this->fIn1   = $fInput;
            $this->fOut2  = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $fc * $this->fOut2;  // Pole 2
            $this->fIn2   = $this->fOut1;
            $this->fOut3  = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $fc * $this->fOut3;  // Pole 3
            $this->fIn3   = $this->fOut2;
            $this->fOut4  = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $fc * $this->fOut4;  // Pole 4
            $this->fIn4   = $this->fOut3;
            $oSamples[$i] = $this->fOut4;
        }
    }

    /**
     * filter for fixed Q, varing F
     */
    protected function filterVaryingF(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $fa = $this->oCutoff[$i] * self::F_COEFF_1;
            $fb = $fResonance * (1.0 - self::F_COEFF_2 * $fa * $fa);
            $fc = 1.0 - $fa;
            $fInput      -= $this->fOut4 * $fb;
            $fInput      *= self::F_COEFF_3 * ($fa * $fa * $fa * $fa);
            $this->fOut1  = $fInput + self::F_TAP_SCALE * $this->fIn1 + $fc * $this->fOut1; // Pole 1
            $this->fIn1   = $fInput;
            $this->fOut2  = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $fc * $this->fOut2;  // Pole 2
            $this->fIn2   = $this->fOut1;
            $this->fOut3  = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $fc * $this->fOut3;  // Pole 3
            $this->fIn3   = $this->fOut2;
            $this->fOut4  = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $fc * $this->fOut4;  // Pole 4
            $this->fIn4   = $this->fOut3;
            $oSamples[$i] = $this->fOut4;
        }
    }

    /**
     * filter for fixed F, varing Q
     */
    protected function filterVaryingQ(SPLFixedArray $oSamples) {
        $fa = $this->fCutoff * self::F_COEFF_1;
        $fc = 1 - $fa;
        foreach ($oSamples as $i => $fInput) {
            $fb = $this->oResonance[$i] * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $fa * $fa);
            $fInput      -= $this->fOut4 * $fb;
            $fInput      *= self::F_COEFF_3 * ($fa * $fa * $fa * $fa);
            $this->fOut1  = $fInput + self::F_TAP_SCALE * $this->fIn1 + $fc * $this->fOut1; // Pole 1
            $this->fIn1   = $fInput;
            $this->fOut2  = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $fc * $this->fOut2;  // Pole 2
            $this->fIn2   = $this->fOut1;
            $this->fOut3  = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $fc * $this->fOut3;  // Pole 3
            $this->fIn3   = $this->fOut2;
            $this->fOut4  = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $fc * $this->fOut4;  // Pole 4
            $this->fIn4   = $this->fOut3;
            $oSamples[$i] = $this->fOut4;
        }
    }

    /**
     * filter for varing F and varying Q
     */
    protected function filterVaryingFQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $fa = $this->oCutoff[$i]    * self::F_COEFF_1;
            $fb = $this->oResonance[$i] * self::F_SCALE_MAX_Q * (1.0 - self::F_COEFF_2 * $fa * $fa);
            $fc = 1 - $fa;
            $fInput      -= $this->fOut4 * $fb;
            $fInput      *= self::F_COEFF_3 * ($fa * $fa * $fa * $fa);
            $this->fOut1  = $fInput + self::F_TAP_SCALE * $this->fIn1 + $fc * $this->fOut1; // Pole 1
            $this->fIn1   = $fInput;
            $this->fOut2  = $this->fOut1 + self::F_TAP_SCALE * $this->fIn2 + $fc * $this->fOut2;  // Pole 2
            $this->fIn2   = $this->fOut1;
            $this->fOut3  = $this->fOut2 + self::F_TAP_SCALE * $this->fIn3 + $fc * $this->fOut3;  // Pole 3
            $this->fIn3   = $this->fOut2;
            $this->fOut4  = $this->fOut3 + self::F_TAP_SCALE * $this->fIn4 + $fc * $this->fOut4;  // Pole 4
            $this->fIn4   = $this->fOut3;
            $oSamples[$i] = $this->fOut4;
        }
    }
}
