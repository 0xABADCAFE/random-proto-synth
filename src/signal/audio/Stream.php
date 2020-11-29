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

namespace ABadCafe\Synth\Signal\Audio\Stream;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Tag interface for classes are Signal\Audio\IStream and in turn consume Signal\Audio\IStream
 */
interface Processor extends Signal\Audio\IStream {

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IMixer
 *
 * Top level interface for audio stream mixers
 */
interface IMixer extends Processor {

    /**
     * Add a named input stream to the mix. If a stream already exists with then given name, it will be replaced.
     *
     * @param  string               $sName
     * @param  Signal\Audio\IStream $oStream
     * @param  float                $fInitialLevel
     * @return self
     */
    public function addStream(string $sName, Signal\Audio\IStream $oStream, float $fInitialLevel) : self;

    /**
     * Removes a named input stream. No errors are raised if the named stream does not exist.
     *
     * @param  string $sName
     * @return self
     */
    public function removeStream(string $sName) : self;

    /**
     * Returns true if the mixer has no active inputs
     *
     * @return bool
     */
    public function isSilent() : bool;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IAmplifier
 *
 * Tag interface for Amplifiers
 */
interface IAmplifier extends Processor {

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IFilter
 *
 * Main interface for audio stream filters.
 */
interface IFilter extends Processor {
    const
        F_MIN_CUTOFF    = 0.001,
        F_DEF_CUTOFF    = 0.5,
        F_MAX_CUTOFF    = 1.0,
        F_MIN_RESONANCE = 0.0,
        F_DEF_RESONANCE = 0.0,
        F_MAX_RESONANCE = 1.0
    ;

    /**
     * Sets the cutoff, in the range self::F_MIN_CUTOFF ... self::F_MAX_CUTOFF. Values outside this range are clamped,
     *
     * @param  float $fCutoff
     * @return self
     */
    public function setFixedCutoff(float $fCutoff) : self;

    /**
     * @return float
     */
    public function getFixedCutoff() : float;

    /**
     * Sets the resonance, in the range self::F_MIN_RESONANCE ... self::F_MAX_RESONANCE. Values outside this range are clamped.
     * Uses a normalised scale in which 1.0 is the highest setting supported by the filter before self-oscillation or other
     * chaotic behaviours emerge. Zero implies no resonance.
     *
     * @param  float $fCutoff
     * @return self
     */
    public function setFixedResonance(float $fResonance) : self;

    /**
     * @return float
     */
    public function getFixedResonance() : float;

    /**
     * Sets a control stream to use for cutoff. Setting null reverts back to fixed cutoff.
     *
     * @param  Signal\Control\IStream|null $oCutoffControl
     * @return self
     */
    public function setCutoffControl(?Signal\Control\IStream $oCutoffControl) : self;

    /**
     * @return Signal\Control\IStream|null
     */
    public function getCutoffControl() : ?Signal\Control\IStream;

    /**
     * Sets a control stream to use for resonance. Setting null reverts back to fixed resonance.
     *
     * @param  Signal\Control\IStream|null $oResonanceControl
     * @return self
     */
    public function setResonanceControl(?Signal\Control\IStream $oResonanceControl) : self;

    /**
     * @return Signal\Control\IStream|null
     */
    public function getResonanceControl() : ?Signal\Control\IStream;
}
