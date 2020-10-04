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

namespace ABadCafe\Synth\Signal\Generator;

/**
 * Interface for per-sample manipulation of shape and amplitude within an IGenerator implementation.
 */
interface IWaveShaper {
    /**
     * Called for time domain input value in the mapping, applies some modification (eg phase, etc)
     *
     * @param float $fInput
     */
    public function modifyInput(float $fInput) : float;

    /**
     * Called for the amplitude domain output in the mapping, applies some modification.
     *
     * @param float $fOutput
     */
    public function modifyOutput(float $fOutput) : float;
}
