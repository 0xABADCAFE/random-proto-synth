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

namespace ABadCafe\Synth\Signal;
use \SPLFixedArray;

/**
 * TPacketImplementation
 *
 * Mixin to provide common Packet operations
 */
trait TPacketImplementation {

    /** @var SPLFixedArray $oEmpty */
    private static ?SPLFixedArray $oEmpty = null;

    /** @var SPLFixedArray */
    private SPLFixedArray $oValues;

    /**
     * Constructor. Accepts either an integer length for a new, zero initialised packet, or an array of values that
     * define the packet data.
     *
     * @param int|float[] $mInput
     */
    public function __construct(SPLFixedArray $oValues = null) {
        $this->oValues = $oValues ?: self::initEmpty();
    }

    /**
     * Packet cloning must ensure that the internal fixed array instance is cloned
     */
    public function __clone() {
        $this->oValues = clone $this->oValues;
    }

    /**
     * Fill the packet with a given value
     *
     * @param  float  $fValue
     * @return Packet fluent
     */
    public function fillWith(float $fFill) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fFill;
        }
        return $this;
    }

    /**
     * Adjust all values in the packet by a given value
     *
     * @param  float  $fBias
     * @return Packet fluent
     */
    public function biasBy(float $fBias) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $fBias;
        }
        return $this;
    }

    /**
     * Multiply all values in the packet by a given value
     *
     * @param  float  $fValue
     * @return Packet fluent
     */
    public function scaleBy(float $fScale) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $fScale;
        }
        return $this;
    }

    /**
     * Multiply the values in this packet with those in the provided packet
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function modulateWith(self $oPacket) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $oPacket->oValues[$i];
        }
        return $this;
    }

    /**
     * Sum the values in this packet with those in the provided packet
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function sumWith(self $oPacket) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $oPacket->oValues[$i];
        }
        return $this;
    }

    /**
     * Sum the values in this packet with the values in another, premultiplied by the scale
     *
     * @param  Packet $oPacket
     * @param  float  $fScale
     * @return Packet fluent
     */
    public function accumulate(self $oPacket, float $fScale) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $oPacket->oValues[$i] * $fScale;
        }
        return $this;
    }

    /**
     * Subtract the values in the provided packed with those in this packet
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function diffWith(self $oPacket) : self {
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue - $oPacket->oValues[$i];
        }
        return $this;
    }

    /**
     * @return SPLFixedArray
     */
    public function getValues() : SPLFixedArray {
        return $this->oValues;
    }

    /**
     * Obtain the values scaled and quantized to some fixed integer range (e.g. conversion to sint16 for final output)
     *
     * @return SPLFixedArray
     */
    public function quantise(int $iScaleValue, int $iMinValue, int $iMaxValue) : SPLFixedArray {
        $oResult = clone $this->oValues;
        foreach ($oResult as $i => $mValue) {
            $mValue = (int)($mValue * $iScaleValue);
            $oResult[$i] = $mValue < $iMinValue ? $iMinValue : ($mValue > $iMaxValue ? $iMaxValue : $mValue);
        }
        return $oResult;
    }

    /**
     * Get a zero filled SPLFixedArray
     *
     * @return SPLFixedArray
     */
    private static function initEmpty() : SPLFixedArray {
        if (!self::$oEmpty) {
            self::$oEmpty = SPLFixedArray::fromArray(array_fill(0, Context::get()->getPacketLength(), 0.0));
        }
        return clone self::$oEmpty;
    }
}
