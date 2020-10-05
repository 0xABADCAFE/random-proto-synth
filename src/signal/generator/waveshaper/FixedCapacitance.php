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
 * FixedCapacitance
 *
 * Adds a charge/discharge like slew to the sample stream based on blending with previous values.
 */
class FixedCapacitance implements Generator\IWaveShaper {

    const F_DEFAULT_AMOUNT = 0.5;

    private float
        $fLast = 0.0,
        $fLastScale,
        $fNextScale
    ;

    public function __construct(float $fAmount = self::F_DEFAULT_AMOUNT) {
        $this->fLastScale  = $fAmount;
        $this->fNextScale = 1.0 - $this->fLastScale;
    }

    /**
     * @inheritDoc
     */
    public function modifyInput(float $fInput) : float {
        return $fInput;
    }

    /**
     * @inheritDoc
     */
    public function modifyOutput(float $fOutput) : float {
        return $this->fLast = ($this->fNextScale * $fOutput) + ($this->fLastScale * $this->fLast);
    }
}
