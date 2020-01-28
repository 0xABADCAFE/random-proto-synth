<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use \SPLFixedArray;

/**
 * IGenerator
 *
 * Main function generator interface. Function generators generate a basic waveform, with a time-independent duty
 * cycle of 0.0 - 1.0. Values outside this range will have their integer part ignored.
 */
interface IGenerator {

    /**
     * Returns the period of this function, i.e. the numeric interval after which it's output cycles.
     *
     * @return float
     */
    public function getPeriod() : float;

    /**
     * Calculate a Packets worth of output values for a Packets worth of input values
     *
     * @param Packet $oInput
     * @return Packet
     *
     */
    public function map(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * DC - simplest spectral complexity (zero frequencies)
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
     * @return DC fluent
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
 * Sine - single frequency
 *
 * Maps input values to a sine wave output.
 */
class Sine implements IGenerator {

    const F_PERIOD = 2.0 * M_PI;

    private
        $fMinLevel,
        $fScaleLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = ILimits::F_MAX_LEVEL_NO_CLIP
    ) {
        $this->fMinLevel   = ($fMaxLevel + $fMinLevel)/2;
        $this->fScaleLevel = ($fMaxLevel - $fMinLevel)/2;
    }

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
            $oValues[$i] = ($this->fScaleLevel * sin($fValue)) + $this->fMinLevel;
        }
        return $oOutput;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Square - series of frequencies
 *
 * Maps input values to a square output.
 */
class Square implements IGenerator {

    const F_PERIOD = 2.0;

    private
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
        $this->fMinLevel = $fMinLevel;
        $this->fMaxLevel = $fMaxLevel;
    }

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
class SawUp implements IGenerator {
    const F_PERIOD  = 1.0;

    protected
        $fMinLevel,
        $fScaleLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = ILimits::F_MAX_LEVEL_NO_CLIP
    ) {
        $this->fMinLevel   = $fMinLevel;
        $this->fScaleLevel = $fMaxLevel - $fMinLevel;
    }

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
            $oValues[$i] = -($this->fScaleLevel * ($fValue - floor($fValue)) + $this->fMinLevel);
        }
        return $oOutput;
    }

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Basic triangle generator
 */
class Triangle implements IGenerator {

    const F_PERIOD = 2.0;

    protected
        $fMinLevel,
        $fScaleLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = ILimits::F_MAX_LEVEL_NO_CLIP
    ) {
        $this->fMinLevel   = $fMinLevel;
        $this->fScaleLevel = $fMaxLevel - $fMinLevel;
    }


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
            $fSign  = (int)$fFloor & 1 ? 1 : -1;
            $oValues[$i] = $fSign*($this->fScaleLevel * ($fValue - $fFloor) + $this->fMinLevel);
        }
        return $oOutput;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Noise - all frequencies
 *
 * Maps to a randomised value, irrespective of input
 */
class Noise implements IGenerator {

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
        static $fNormalize = null;
        if (null === $fNormalize) {
            $fNormalize = (ILimits::F_MAX_LEVEL_NO_CLIP - ILimits::F_MIN_LEVEL_NO_CLIP) / (float)mt_getrandmax();
        }

        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach($oValues as $i => $fValue) {
            $oValues[$i] = ILimits::F_MIN_LEVEL_NO_CLIP + mt_rand() * $fNormalize;
        }
        return $oOutput;
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
