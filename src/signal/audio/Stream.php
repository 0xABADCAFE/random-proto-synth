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

