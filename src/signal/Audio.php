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
namespace ABadCafe\Synth\Signal\Audio;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Concrete Audio Packet
 *
 */
class Packet implements Signal\IPacket {
   use Signal\TPacketImplementation;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for audio stream generators.
 *
 */
interface IStream extends Signal\IStream {

    /**
     * Reset the stream
     */
    public function reset() : self; // Covariant return

    /**
     * Emit a Packet
     */
    public function emit() : Packet; // Covariant return
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IFilter
 *
 * Main audio signal filter interface. The filter cutoff is normalised such that the range 0.0 - 1.0 covers the full
 * frequency range.
 */
interface IFilter {
    const
        F_MIN_CUTOFF = 0.001,
        F_DEF_CUTOFF = 0.5,
        F_MAX_CUTOFF = 1.0
    ;

    /**
     * Reset the filter, re-initialising all internal state.
     *
     * @return self.
     */
    public function reset() : self;

    /**
     * Set the cutoff. Uses a normalied scale in which 1.0 is the highest stable setting
     * supported by the filter.
     *
     * @param  float $fCutoff - 0 < $fCutoff <= 1.0
     * @return self
     */
    public function setCutoff(float $fCutoff) : self;

    /**
     * Get the cutoff. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getCutoff() : float;

    /**
     * Filter a Packet
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function filter(Packet $oInput) : Packet;
}

require_once 'audio/Filter.php';
