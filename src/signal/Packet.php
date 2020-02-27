<?php

namespace ABadCafe\Synth\Signal;
use \SPLFixedArray;
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Packet
 *
 * Represents a packet of a signal. All Packet instances have the same length, configured in Signal\Context
 */
class Packet {

    const
        // Limits
        MIN_CHANNELS = 1,
        MAX_CHANNELS = 2,

        // Mode alieases
        CH_MONO   = 1,
        CH_STEREO = 2
    ;

    /** @var SPLFixedArray[] $aEmpty */
    private static $aEmpty = [];

    /** @var SPLFixedArray $oValues */
    private $oValues = null;

    /** @var int $iChannels */
    private $iChannels = self::MIN_CHANNELS;


    /**
     * Constructor. Accepts the number of channels in this Packet instance. Default is MIN_CHANNELS.
     *
     * @param int $iChannels
     */
    public function __construct(int $iChannels = self::CH_MONO) {
        $this->iChannels = clamp($iChannels, self::MIN_CHANNELS, self::MAX_CHANNELS);
        $this->oValues   = self::initEmpty($this->iChannels);
    }

    /**
     * Packet cloning must ensure that the internal fixed array instance is cloned.
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
     * Adjust all values in the packet by a given value.
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
     * Multiply all values in the packet by a given value.
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
    public function modulateWith(Packet $oPacket) : self {
        if ($oPacket->iChannels == $this->iChannels) {
            foreach ($this->oValues as $i => $fValue) {
                $this->oValues[$i] = $fValue * $oPacket->oValues[$i];
            }
        }
        return $this;
    }

    /**
     * Sum the values in this packet with those in the provided packet
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function sumWith(Packet $oPacket) : self {
        if ($oPacket->iChannels == $this->iChannels) {
            foreach ($this->oValues as $i => $fValue) {
                $this->oValues[$i] = $fValue + $oPacket->oValues[$i];
            }
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
    public function accumulate(Packet $oPacket, float $fScale) : self {
        if ($oPacket->iChannels == $this->iChannels) {
            foreach ($this->oValues as $i => $fValue) {
                $this->oValues[$i] = $fValue + $oPacket->oValues[$i] * $fScale;
            }
        }
        return $this;
    }

    /**
     * Subtract the values in the provided packed with those in this packet
     *
     * @param  Packet $oPacket
     * @return Packet fluent
     */
    public function diffWith(Packet $oPacket) : self {
        if ($oPacket->iChannels == $this->iChannels) {
            foreach ($this->oValues as $i => $fValue) {
                $this->oValues[$i] = $fValue - $oPacket->oValues[$i];
            }
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
     * @return int
     */
    public function getChannels() : int {
        return $this-iChannels;
    }

    /**
     * Obtain the values scaled and quantized to some fixed integer range (e.g. conversion to sint16 for final output)
     *
     * @return SPLFixedArray
     */
    public function quantize(int $iScaleValue, int $iMinValue, int $iMaxValue) : SPLFixedArray {
        $oResult = clone $this->oValues;
        foreach ($oResult as $i => $mValue) {
            $mValue = (int)($mValue * $iScaleValue);
            $oResult[$i] = $mValue < $iMinValue ? $iMinValue : ($mValue > $iMaxValue ? $iMaxValue : $mValue);
        }
        return $oResult;
    }

    /**
     * Get a zero filled SPLFixedArray, by cloning a prototype.
     *
     * @return SPLFixedArray
     */
    private static function initEmpty(int $iChannels) : SPLFixedArray {
        if (!isset(self::$aEmpty[$iChannels])) {
            self::$aEmpty[$iChannels] = SPLFixedArray::fromArray(array_fill(0, $iChannels * Context::get()->getPacketLength(), 0.0));
        }
        return clone self::$aEmpty[$iChannels];
    }
}
