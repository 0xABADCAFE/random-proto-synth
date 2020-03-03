<?php

namespace ABadCafe\Synth\Signal;

use \SPLFixedArray;

use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * TPacket
 *
 * Trait to allow generalisation of Packets
 */
trait TPacket {

    /** @var SPLFixedArray $oValues */
    private $oValues = null;

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
     * @return self   fluent
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
     * @return self   fluent
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
     * @return self   fluent
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
     * @return fluent fluent
     */
    public function modulateWith(self $oPacket) : self {
        $oValues = $oPacket->oValues;
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $oValues[$i];
        }
        return $this;
    }

    /**
     * Sum the values in this packet with those in the provided packet
     *
     * @param  self $oPacket
     * @return self fluent
     */
    public function sumWith(self $oPacket) : self {
        $oValues = $oPacket->oValues;
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue + $oValues[$i];
        }
        return $this;
    }

    /**
     * Sum the values in this packet with the values in another, premultiplied by the scale
     *
     * @param  self   $oPacket
     * @param  float  $fScale
     * @return self   fluent
     */
    public function accumulate(self $oPacket, float $fScale) : self {
        $oValues = $oPacket->oValues;
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
}
