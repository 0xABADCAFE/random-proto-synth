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

namespace ABadCafe\Synth\Map;
use \SPLFixedArray;
use \OutOfRangeException;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Limits for MIDI Maps
 */
interface IMIDIByteLimits {
    const
        I_MIN_SINGLE_BYTE_VALUE = 0,
        I_MAX_SINGLE_BYTE_VALUE = 127
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * MIDIByteMap
 *
 * Base class for MIDI based byte maps, in which a byte number maps to some controlling value.
 */
abstract class MIDIByte implements IMIDIByteLimits {

    protected SPLFixedArray $oMap;

    /**
     * Constructor
     *
     * Ensures that the internal array is created and filled
     */
    public function __construct() {
        $this->oMap = new SPLFixedArray(self::I_MAX_SINGLE_BYTE_VALUE + 1);
        $this->populateMap();
    }

    /**
     * Map an input byte to a value. The value must be expressable as a floating point number but the meaning of
     * the value depends on what the Map contains.
     *
     * @param  int $iByte
     * @return float
     * @throws OutOfRangeException
     */
    public function mapByte(int $iByte) : float {
        $this->assertByteRange($iByte);
        return $this->oMap[$iByte];
    }

    /**
     * Ensure the byte value is in the legal range. Throws OutOfRangeException for values outside the range.
     *
     * @param  int $iByte
     * @throws OutOfRangeException
     */
    protected function assertByteRange(int $iByte) {
        if (
            $iByte < self::I_MIN_SINGLE_BYTE_VALUE ||
            $iByte > self::I_MAX_SINGLE_BYTE_VALUE
        ) {
            throw new OutOfRangeException();
        }
    }

    /**
     * Fill in the internal map.
     */
    protected abstract function populateMap();
}

