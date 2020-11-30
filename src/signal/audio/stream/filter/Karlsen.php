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
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Karlsen fast ladder filters
 *
 * Based on http://www.musicdsp.org/en/latest/Filters/141-karlsen.html
 */
abstract class Karlsen extends Base {

    const
        F_SCALE_MAX_Q  = 4.0 // The original goes to 50, but it's way OTT.
    ;

     protected float
        $fPole1    = 0.0, $fPole2 = 0.0, $fPole3 = 0.0, $fPole4 = 0.0,
        $fFeedback = 0.0
    ;

    /**
     * @inheritdoc
     */
    public function reset() : self {
        parent::reset();
        $this->fPole1    = 0.0;
        $this->fPole2    = 0.0;
        $this->fPole3    = 0.0;
        $this->fPole4    = 0.0;
        $this->fFeedback = 0.0;
        return $this;
    }

    /**
     * Filter a single sample. Uses 2x oversampling and dynamic feedback. The sample data enters the filter poles
     * and the various sums and differences of those afterwards can be used to recover the output of interest.
     *
     * @param float $fInput
     * @param float $fCutoff
     * @param float $fResonance
     */
    protected function filterSample(float $fInput, float $fCutoff, float $fResonance) {
        $fInputSH    = $fInput;
        $iOverSample = 2;
        $iInvCutoff  = 1.0 - $fCutoff;
        while ($iOverSample--) {
            $fPrevFeedback   = $this->fFeedback > 1.0 ? 1.0 : $this->fFeedback;
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
            $this->fPole2 = ($this->fPole1 * $fCutoff) + ($this->fPole2 * $iInvCutoff); // pole 2
            $this->fPole3 = ($this->fPole2 * $fCutoff) + ($this->fPole3 * $iInvCutoff); // pole 3
            $this->fPole4 = ($this->fPole3 * $fCutoff) + ($this->fPole4 * $iInvCutoff); // pole 4
        }
    }

}
