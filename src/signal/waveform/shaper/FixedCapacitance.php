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

namespace ABadCafe\Synth\Signal\Waveform\Shaper;
use ABadCafe\Synth\Signal\Waveform;

/**
 * FixedCapacitance
 *
 * Adds a charge/discharge like slew to the sample stream based on blending with previous values.
 */
class FixedCapacitance implements Waveform\IShaper {

    const F_DEFAULT_AMOUNT = 0.5;

    private float
        $fLast = 0.0,
        $fLastScale,
        $fNextScale
    ;

    /**
     * @param float $fAmount
     */
    public function __construct(float $fCapacitance = self::F_DEFAULT_AMOUNT) {
        $this->fLastScale  = $fCapacitance;
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
