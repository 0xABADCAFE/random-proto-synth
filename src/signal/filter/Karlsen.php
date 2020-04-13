<?php

declare(strict_types = 1);

namespace ABadCafe\Synth\Signal\Filter;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Karlsen fast ladder filters
 *
 * Based on http://www.musicdsp.org/en/latest/Filters/141-karlsen.html
 */
abstract class Karlsen extends Resonant {

    const
        F_SCALE_MAX_Q  = 4.0 // The original goes to 50, but it's way OTT.
    ;

     protected float
        $fInitCutoff,
        $fInitResonance,
        $fPole1, $fPole2, $fPole3, $fPole4,
        $fFeedback
    ;

    public function __construct(float $fCutoff = self::F_DEF_CUTOFF, float $fResonance = self::F_DEF_RESONANCE) {
        $this->setCutoff($fCutoff);
        $this->setResonance($fResonance);
        $this->fInitCutoff    = $this->fCutoff;
        $this->fInitResonance = $this->fResonance;
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function reset() : Signal\IFilter {
        parent::reset();
        $this->fPole1      = 0.0;
        $this->fPole2      = 0.0;
        $this->fPole3      = 0.0;
        $this->fPole4      = 0.0;
        $this->fFeedback   = 0.0;
        $this->fCutoff     = $this->fInitCutoff;
        $this->fResonance  = $this->fInitResonance;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function filter(Signal\Packet $oInput) : Signal\Packet {
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
     * Filter a single sample. This should probably be inlined in the same way as the basic ResonantLowPass example
     */
    protected function filterSample(float $fInput, float $fCutoff, float $fResonance) {
        $fInputSH    = $fInput;
        $iOverSample = 2;
        $iInvCutoff  = 1.0 - $fCutoff;
        while ($iOverSample--) {
            $fPrevFeedback = $this->fFeedback > 1.0 ? 1.0 : $this->fFeedback;

            $this->fFeedback = ($this->fFeedback * 0.418) + (($fResonance * $this->fPole4) * 0.582); // dynamic feedback
            $fFeedbackPhase  = ($this->fFeedback * 0.36)  + ($fPrevFeedback * 0.64);                 // feedback phase
            $fInput          = $fInputSH - $fFeedbackPhase;                                          // inverted feedback

            $this->fPole1 = ($fInput   * $fCutoff) + ($this->fPole1 * $iInvCutoff);             // pole 1
            if ($this->fPole1 > 1.0) {
                $this->fPole1 = 1.0;
            }
            else if ($this->fPole1 < -1.0) {
                $this->fPole1 = -1.0;
            }  // pole 1 clipping
            $this->fPole2 = ($this->fPole1 * $fCutoff) + ($this->fPole2 * $iInvCutoff);        // pole 2
            $this->fPole3 = ($this->fPole2 * $fCutoff) + ($this->fPole3 * $iInvCutoff);        // pole 3
            $this->fPole4 = ($this->fPole3 * $fCutoff) + ($this->fPole4 * $iInvCutoff);        // pole 4
        }
    }

    /**
     * filter for fixed F/Q
     */
    protected abstract function filterFlat(SPLFixedArray $oSamples);

    /**
     * filter for fixed Q, varing F
     */
    protected abstract function filterVaryingF(SPLFixedArray $oSamples);

    /**
     * filter for fixed F, varing Q
     */
    protected abstract function filterVaryingQ(SPLFixedArray $oSamples);

    /**
     * filter for varing F and varying Q
     */
    protected abstract function filterVaryingFQ(SPLFixedArray $oSamples);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KarlsenLowPass Low Pass filter
 */
class KarlsenLowPass extends Karlsen {

    /**
     * filter for fixed F/Q
     */
    protected function filterFlat(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, $fResonance);
            $oSamples[$i] = $this->fPole4;
        }
    }

    /**
     * filter for fixed Q, varing F
     */
    protected function filterVaryingF(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], $fResonance);
            $oSamples[$i] = $this->fPole4;
        }
    }

    /**
     * filter for fixed F, varing Q
     */
    protected function filterVaryingQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $this->fPole4;
        }
    }

    /**
     * filter for varing F and varying Q
     */
    protected function filterVaryingFQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $this->fPole4;
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KarlsenBandPass Low Pass filter
 */
class KarlsenBandPass extends Karlsen {

    /**
     * filter for fixed F/Q
     */
    protected function filterFlat(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, $fResonance);
            $oSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for fixed Q, varing F
     */
    protected function filterVaryingF(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], $fResonance);
            $oSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for fixed F, varing Q
     */
    protected function filterVaryingQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for varing F and varying Q
     */
    protected function filterVaryingFQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $this->fPole4 - $this->fPole1;
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KarlsenHighPass Low Pass filter
 */
class KarlsenHighPass extends Karlsen {

    /**
     * filter for fixed F/Q
     */
    protected function filterFlat(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, $fResonance);
            $oSamples[$i] = $fInput - $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for fixed Q, varing F
     */
    protected function filterVaryingF(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], $fResonance);
            $oSamples[$i] = $fInput - $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for fixed F, varing Q
     */
    protected function filterVaryingQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $fInput - $this->fPole4 - $this->fPole1;
        }
    }

    /**
     * filter for varing F and varying Q
     */
    protected function filterVaryingFQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $fInput - $this->fPole4 - $this->fPole1;
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KarlsenNotchReject Low Pass filter
 */
class KarlsenNotchReject extends Karlsen {

    /**
     * filter for fixed F/Q
     */
    protected function filterFlat(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, $fResonance);
            $oSamples[$i] = $fInput - $this->fPole1;
        }
    }

    /**
     * filter for fixed Q, varing F
     */
    protected function filterVaryingF(SPLFixedArray $oSamples) {
        $fResonance = $this->fResonance  * self::F_SCALE_MAX_Q;
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], $fResonance);
            $oSamples[$i] = $fInput - $this->fPole1;
        }
    }

    /**
     * filter for fixed F, varing Q
     */
    protected function filterVaryingQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->fCutoff, self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $fInput - $this->fPole1;
        }
    }

    /**
     * filter for varing F and varying Q
     */
    protected function filterVaryingFQ(SPLFixedArray $oSamples) {
        foreach ($oSamples as $i => $fInput) {
            $this->filterSample($fInput, $this->oCutoff[$i], self::F_SCALE_MAX_Q * $this->oResonance[$i]);
            $oSamples[$i] = $fInput - $this->fPole1;
        }
    }
}
