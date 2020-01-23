<?php

namespace ABadCafe\Synth\Map;

use \SPLFixedArray;
use \OutOfRangeException;

/**
 * Limits for MIDI Maps
 */
interface IMIDIByteMapLimits {
    const
        I_MIN_SINGLE_BYTE_VALUE = 0,
        I_MAX_SINGLE_BYTE_VALUE = 127
    ;
}

/**
 * MIDIByteMap
 *
 * Base class for MIDI based byte maps
 */
abstract class MIDIByteMap implements IMIDIByteMapLimits {

    /** @var SPLFixedArray $oMap */
    protected $oMap;

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
     * Map an input byte to a value. There is no return type enforced as the value will be entirely dependent on what the map contains.
     *
     * @param  int $iByte
     * @return mixed
     * @throws OutOfRangeException
     */
    public function mapByte(int $iByte) {
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

