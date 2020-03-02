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
class Packet implements IChannelMode {

    /** @var SPLFixedArray[] $aEmpty */
    private static $aEmpty = [];

    /** @var SPLFixedArray $oValues */
    private $oValues = null;

    /** @var int $iChannelMode */
    private $iChannelMode = self::I_CHAN_MONO;


    /**
     * Constructor. Accepts the number of channels in this Packet instance. Default is MIN_CHANNELS.
     *
     * @param int $iChannelMode
     */
    public function __construct(int $iChannelMode = self::I_CHAN_MONO) {
        $this->iChannelMode = clamp($iChannelMode, self::I_CHAN_MONO, self::I_CHAN_STEREO);
        $this->oValues   = self::initEmpty($this->iChannelMode);
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
        $oValues = $this->extractValues($oPacket);
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $oValues[$i];
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
        $oValues = $this->extractValues($oPacket);
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $oValues[$i];
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
        $oValues = $this->extractValues($oPacket);
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $oValues[$i] * $fScale;
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
    public function getChannelMode() : int {
        return $this->iChannelMode;
    }

    /**
     * Get the values for a Packet, ensuring that they are in the same channel mode as this one.
     */
    private function extractValues(Packet $oPacket) : SPLFixedArray {
        if ($this->iChannelMode == $oPacket->iChannelMode) {
            return $oPacket->oValues;
        }
        $oValues = clone $this->oValues;
        $i = 0;
        if ($this->iChannelMode > $oPacket->iChannelMode) {
            // Split into stereo
            foreach ($oPacket->oValues as $fValue) {
                $oValues[$i++] = $fValue;
                $oValues[$i++] = $fValue;
            }
        } else {
            // Merge into mono
            foreach ($oValues as $j => $fValue) {
                $oValues[$j] = 0.5 * ($oPacket->oValues[$i++] + $oPacket->oValues[$i++]);
            }
        }
        return $oValues;
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
    private static function initEmpty(int $iChannelMode) : SPLFixedArray {
        if (!isset(self::$aEmpty[$iChannelMode])) {
            self::$aEmpty[$iChannelMode] = SPLFixedArray::fromArray(array_fill(0, $iChannelMode * Context::get()->getPacketLength(), 0.0));
        }
        return clone self::$aEmpty[$iChannelMode];
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Type hintable front for specifically mono packet users
 */
class MonoPacket extends Packet {
    public function __construct() {
        parent::__construct(self::I_CHAN_MONO);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Type hintable front for specifically stereo packet users
 */
class StereoPacket extends Packet {
    public function __construct() {
        parent::__construct(self::I_CHAN_STEREO);
    }
}
