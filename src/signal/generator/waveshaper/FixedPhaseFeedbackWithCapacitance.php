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

namespace ABadCafe\Synth\Signal\Generator\WaveShaper;
use ABadCafe\Synth\Signal\Generator;

/**
 * FixedPhaseFeedbackWithCapacitance
 *
 * Combines Phase Feedback with Capacitance
 */
class FixedPhaseFeedbackWithCapacitance implements Generator\IWaveShaper {

    protected float
        $fFeedback         = 0.1,
        $fLastOne          = 0.0,
        $fLastTwo          = 0.0,
        $fLastScale,
        $fNextScale
    ;

    /**
     * @param float $fFeedback
     * @param float $fCapacitance
     */
    public function __construct(float $fFeedback, float $fCapacitance) {
        $this->fFeedback  = $fFeedback * 0.5;
        $this->fLastScale = $fCapacitance;
        $this->fNextScale = 1.0 - $this->fLastScale;
    }

    /**
     * @inheritDoc
     */
    public function modifyInput(float $fInput) : float {
        return $fInput + $this->fFeedback * ($this->fLastOne + $this->fLastTwo);
    }

    /**
     * @inheritDoc
     */
    public function modifyOutput(float $fOutput) : float {
        $fOutput = ($this->fNextScale * $fOutput) + ($this->fLastScale * $this->fLastOne);
        $this->fLastTwo = $this->fLastOne;
        $this->fLastOne = $fOutput;
        return $fOutput;
    }
}
