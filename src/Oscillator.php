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
 * Oscillator
 */
namespace ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOscillator
 *
 * Interface for Oscillators
 */
interface IOscillator {

    /**
     * Get the oscillator frequency in Hz
     *
     * @return float
     */
    public function getFrequency() : float;

    /**
     * Set a new frequency for the oscillator, in Hz
     *
     * @param  float $fFrequecny
     * @return self
     */
    public function setFrequency(float $fFrequency) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'oscillator/Audio.php';

