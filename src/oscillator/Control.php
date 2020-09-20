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

/**
 * Audio
 */
namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ILimits
 *
 * Defines limits for oscillators data.
 */
interface ILimits {

    const
        /**
         * Frequency Range
         */
        F_MIN_FREQ = 1.0/60.0,
        F_DEF_FREQ = 4.0,         // A4
        F_MAX_FREQ = 110.0        // A2
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOscillator
 *
 * Interface for Control Oscillators (e.g, LFO)
 */
interface IOscillator extends Oscillator\IOscillator, Signal\Control\IStream {

    /**
     * Set the depth of the oscillator.
     *
     * @param  float $fDepth
     * @return self
     */
    public function setDepth(float $fDepth) : self;
}
