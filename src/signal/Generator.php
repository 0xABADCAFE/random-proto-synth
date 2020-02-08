<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IGenerator;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Flat - simplest spectral complexity (zero frequencies)
 *
 * Maps to a fixed value, irrespective of input
 */
class Flat implements IGenerator {

    const F_PERIOD = 1.0;

    /**
     * @var Packet $oOutput
     */
    private $oOutput;

    /**
     * Constructor
     *
     * @param float $fLevel
     */
    public function __construct(float $fLevel = 0) {
        $this->oPacket = new Packet();
        $this->setLevel($fLevel);
    }

    /**
     * Set the level
     *
     * @param  float $fLevel
     * @return self  fluent
     */
    public function setLevel(float $fLevel) : self {
        $this->oPacket->fillWith($fLevel);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     *
     * Input packet has no effect, output is constant.
     */
    public function map(Packet $oInput) : Packet {
        return clone $this->oPacket;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for non-flat generator functions
 */
abstract class NonFlat implements IGenerator {

    protected
        $fMinLevel,
        $fMaxLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = ILimits::F_MAX_LEVEL_NO_CLIP
    ) {
        $this->fMinLevel = min($fMinLevel, $fMaxLevel);
        $this->fMaxLevel = max($fMinLevel, $fMaxLevel);
        $this->init();
    }

    /**
     * Perform any initialisation after setting the levels
     */
    protected function init() {

    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Sine - single frequency
 *
 * Maps input values to a sine wave output.
 */
class Sine extends NonFlat {

    const F_PERIOD = 2.0 * M_PI;

    private
        $fBiasLevel,
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleLevel * sin($fValue)) + $this->fBiasLevel;
        }
        return $oOutput;
    }

    /**
     * @overridden
     */
    protected function init() {
        $this->fBiasLevel  = 0.5*($this->fMaxLevel + $this->fMinLevel);
        $this->fScaleLevel = 0.5*($this->fMaxLevel - $this->fMinLevel);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Square - series of frequencies
 *
 * Maps input values to a square output.
 */
class Square extends NonFlat {

    const F_PERIOD = 2.0;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = floor($fValue) & 1 ? $this->fMinLevel : $this->fMaxLevel;
        }
        return $oOutput;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * SawUp - series of frequencies
 *
 * Maps input values to a upwards sawtooth output.
 */
class SawUp extends NonFlat {
    const F_PERIOD  = 1.0;

    protected
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleLevel * ($fValue - floor($fValue)) + $this->fMinLevel;
        }
        return $oOutput;
    }

    /**
     * @overriden
     */
    protected function init() {
        $this->fScaleLevel = $this->fMaxLevel - $this->fMinLevel;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * SawUp - series of frequencies
 *
 * Maps input values to a downwards sawtooth output.
 */
class SawDown extends SawUp {

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleLevel * (ceil($fValue) - $fValue) + $this->fMinLevel;
        }
        return $oOutput;
    }

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Basic triangle generator
 */
class Triangle extends NonFlat {

    const F_PERIOD = 2.0;

    protected
        $fBiasLevel,
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $fValue -= 0.5;
            $fFloor = floor($fValue);
            $fScale  = (int)$fFloor & 1 ? $this->fScaleLevel : -$this->fScaleLevel;
            $oValues[$i] = $this->fBiasLevel + $fScale*($fValue - $fFloor - 0.5);
        }
        return $oOutput;
    }

    /**
     * @overriden
     */
    protected function init() {
        $this->fBiasLevel  = 0.5*($this->fMaxLevel + $this->fMinLevel);
        $this->fScaleLevel = $this->fMaxLevel - $this->fMinLevel;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Noise - all frequencies
 *
 * Maps to a randomised value, irrespective of input
 */
class Noise extends NonFlat {

    const F_PERIOD = 1.0;

    protected
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach($oValues as $i => $fValue) {
            $oValues[$i] = $this->fMinLevel + mt_rand() * $this->fScaleLevel;
        }
        return $oOutput;
    }

    /**
     * @overriden
     */
    protected function init() {
        $this->fScaleLevel = ($this->fMaxLevel - $this->fMinLevel) / mt_getrandmax();
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * SineQuick - single frequency
 *
 * This is slower than actual Sine() generator but will be useful for the Table
 */
class LookupTest implements IGenerator {

    const I_SIZE_EXP = 8;
    const I_PERIOD   = 1 << self::I_SIZE_EXP;
    const I_MASK     = self::I_PERIOD - 1;

    private static $oTable = null;

    /**
     *
     */
    public function __construct() {
        if (!self::$oTable) {
            $iSize = self::I_PERIOD + 1;
            self::$oTable = new SPLFixedArray($iSize);
            $fScale = 2.0 * M_PI / self::I_PERIOD;
            for ($i = 0; $i < $iSize; $i++) {
                self::$oTable[$i] = sin($fScale * $i);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return (float)self::I_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $iIndex      = (int)floor($fValue);
            $fInterp     = $fValue - $iIndex;
            $iIndex     &= self::I_MASK;
            $oValues[$i] = ((1.0 - $fInterp) * self::$oTable[$iIndex]) + ($fInterp * self::$oTable[$iIndex + 1]);
        }
        return $oOutput;
    }
}

/**
 * TODO
 */
class Table implements IGenerator {

    const F_PERIOD = 1.0;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $oOutput = clone $oInput;
        return $oOutput;
    }
}
