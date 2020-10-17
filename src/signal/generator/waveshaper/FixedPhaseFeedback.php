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
 * PhaseFeedback
 *
 * Allows a self-modulating oscillator to be impmlemented
 */
class FixedPhaseFeedback implements Generator\IWaveShaper {

    const F_DEFAULT_LEVEL = 1.0;

    protected float
        $fFeedback         = 0.1,
        $fLastOne          = 0.0,
        $fLastTwo          = 0.0
    ;

    /**
     * @param float $fFeedback
     */
    public function __construct(float $fFeedback = self::F_DEFAULT_LEVEL) {
        $this->fFeedback = $fFeedback * 0.5;
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
        $this->fLastTwo = $this->fLastOne;
        $this->fLastOne = $fOutput;
        return $fOutput;
    }
}
