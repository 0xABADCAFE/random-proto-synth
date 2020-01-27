<?php

namespace ABadCafe\Synth\Signal\Filter;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IFilter
 *
 * Basic filter interface
 */
interface IFilter {

    const
        F_MIN_CUTOFF = 0.001,
        F_DEF_CUTOFF = 0.5,
        F_MAX_CUTOFF = 1.0
    ;

    /**
     * Reset the filter, re-initialising all internal state.
     *
     * @return self.
     */
    public function reset() : self;

    /**
     * Set the cutoff. Uses a normalied scale in which 1.0 is the highest stable setting
     * supported by the filter.
     *
     * @param  float $fCutoff - 0 < $fCutoff <= 1.0
     * @return self
     */
    public function setCutoff(float $fCutoff) : self;

    /**
     * Get the cutoff. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getCutoff() : float;

    /**
     * Set a control packet for the cutoff. This allows the control of the filter from other signal sources.
     * The values in the packet will be applied on every call to filter() until a new control Packet is set
     * or the existing one is cleared by setting to null. When a control packet is set, it overries whatever
     * the default cutoff has been set to.
     *
     * @param Packet|null $oCutoff
     */
    public function setCutoffControl(Packet $oCutoff = null) : self;

    /**
     * Filter a Packet
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function filter(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IResonant
 *
 * Builds upon the IFilter interface, adding support for
 */
interface IResonant extends IFilter {

    const
        F_MIN_RESONANCE = 0.0,
        F_DEF_RESONANCE = 0.0,
        F_MAX_RESONANCE = 1.0
    ;

    /**
     * Set the resonance level. Uses a normalised scale in which 1.0 is the highest setting
     * supported by the filter before self-oscillation or other chaotic behaviours emerge.
     * Zero implies no resonance.
     *
     * @param  float $fCutoff - 0 < $fResonance <= 1.0
     * @return self
     */
    public function setResonance(float $fResonance) : self;

    /**
     * Get the resonance. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getResonance() : float;

    /**
     * Set a control packet for the resonance. This allows the control of the filter from other signal sources.
     * The values in the packet will be applied on every call to filter() until a new control Packet is set
     * or the existing one is cleared by setting to null. When a control packet is set, it overries whatever
     * the default cutoff has been set to.
     *
     * @param Packet|null $oCutoff
     */
    public function setResonanceControl(Packet $oResonance = null) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Common base class for filter implementations
 */
abstract class Base implements IFilter {
    protected
        /** @var SPLFixedArray $oCutoff */
        $oCutoff,

        /** @var float */
        $fCutoff
    ;

    /**
     * @inheritdoc
     */
    public function reset() : IFilter {
        $this->oCutoff = null;
        $this->fCutoff = self::F_DEF_CUTOFF;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCutoff(float $fCutoff) : IFilter {
        $this->fCutoff = max($fCutoff, self::F_MIN_CUTOFF);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCutoff() : float {
        return $this->fCutoff;
    }

    /**
     * @inheritdoc
     */
    public function setCutoffControl(Packet $oCutoff = null) : IFilter {
        if ($oCutoff) {
            $this->oCutoff = clone $oCutoff->getValues();
        } else {
            $this->oCutoff = null;
        }
        return $this;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Common base class for resonant filter implementations
 */
abstract class Resonant extends Base implements IResonant {
    protected
        /** @var SPLFixedArray $oResonance */
        $oResonance,

        /** @var float $fResonance */
        $fResonance
    ;

    /**
     * @inheritdoc
     */
    public function reset() : IFilter {
        parent::reset();
        $this->oResonance = null;
        $this->fResonance = self::F_DEF_RESONANCE;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResonance(float $fResonance) : IResonant {
        $this->fResonance = max(
            $fResonance,
            self::F_MIN_RESONANCE
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResonance() : float {
        return $this->fResonance;
    }

    /**
     * @inheritdoc
     */
    public function setResonanceControl(Packet $oResonance = null) : IResonant {
        if ($oResonance) {
            $this->oResonance = clone $oResonance->getValues();
        } else {
            $this->oResonance = null;
        }
        return $this;
    }
}


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
