<?php

namespace ABadCafe\Synth\Signal;

use \InvalidArgumentException;
use \RangeException;
use \Countable;

/**
 * ILimits
 *
 * Defines limits for signal data.
 */
interface ILimits {
    const
        F_MIN_NOCLIP = -1.0,
        F_MAX_NOCLIP = 1.0
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Packet
 *
 * Represents a packet of a signal, quantised by sample rate but with continuous (float) intensity
 */
class Packet implements Countable {

    const
        I_MIN_LENGTH = 8,
        I_DEF_LENGTH = 32,
        I_MAX_LENGTH = 256
    ;

    private
        $aSamples,
        $iLength
    ;

    /**
     * Constructor. Accepts either an integer length for a new, zero initialised packet, or an array of values that
     * define the packet data.
     *
     * @param int|float[] $mInput
     */
    public function __construct($mInput = self::I_DEF_LENGTH) {
        if (is_int($mInput)) {
            $this->initFromLength($mInput);
        } else if (is_array($mInput)) {
            $this->initFromArray($mInput);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * Get packet length
     *
     * @return int
     */
    public function count() : int {
        return $this->iLength;
    }

    /**
     * Perform an amplitude modulation with another packet. The values in this packet are multiplied
     * by the values in the supplied packet. Where the incoming packet length is different, only the
     * overlapping region, aligned at the start is processed.
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function multiply(Packet $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] *= $oPacket->aSamples[$i];
        }
        return $this;
    }

    /**
     * Perform an amplitude summation with another packet. The values in this packet are summed with the
     * values in the supplied packet. Where the incoming packet length is different, only the overlapping
     * region, aligned at the start is processed.
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function add(Packet $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] += $oPacket->aSamples[$i];
        }
        return $this;
    }

    /**
     * Perform an amplitude difference with another packet. The values in the supplied packet are subtracted
     * from the values in this packet. Where the incoming packet length is different, only the overlapping
     * region, aligned at the start is processed.
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function subtract(Packet $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] -= $oPacket->aSamples[$i];
        }
        return $this;
    }

    public function getValues() : array {
        return $this->aSamples;
    }

    /**
     * Initialise a new Packet with a given length. All values are floating point zero. Input length must be between
     * I_MIN_LENGTH and I_MAX_LENGTH, otherwise RangeException will be thrown.
     *
     * @param  int $iLength
     * @throws RangeException
     */
    private function initFromLength(int $iLength) {
        $this->assertLengthValid($iLength);
        $this->aSamples = array_fill(0, $iLength, 0.0);
        $this->iLength  = $iLength;
    }

    /**
     * Initialise a new Packet from a raw array of values, which will be mapped to floating point. The input
     * array length must be between I_MAX_LENGTH and I_MAX_LENGTH, otherwise RangeException will be thrown.
     *
     * @param  float[] $aValues
     * @throws RangeException
     */
    private function initFromArray(array $aValues) {
        $iLength = count($aValues);
        $this->assertLengthValid($iLegnth);
        $this->aSamples = array_map('floatval', $aValues);
    }

    /**
     * Checks a proposed length against I_MIN_LENGTH and I_MAX_LENGTH, throwing RangeException if the value falls
     * outside the range defined by I_MIN_LENGTH and I_MAX_LENGTH
     *
     * @param  int $iLength
     * @throws RangeException
     */
    private function assertLengthValid(int $iLength) {
        if ($iLength < self::I_MIN_LENGTH || $iLength > self::I_MAX_LENGTH) {
            throw new RangeException();
        }
    }
}


