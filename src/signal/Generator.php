<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;

class PacketHelper extends Packet {

    public static function cloneFrom(Packet $oInput, array $aValues) : Packet {
        $oOutput = clone $oInput;
        $oOutput->aSamples = $aValues;
        return $oOutput;
    }
}

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
class DC implements IGenerator {

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
    public function setLevel(float $fLevel) : DC {
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
            $oValues[$i] = sin($fValue);
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
            $oValues[$i] = floor($fValue) & 1 ? ILimits::F_MIN_LEVEL_NO_CLIP : ILimits::F_MAX_LEVEL_NO_CLIP;
        }
        return $oOutput;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Saw - series of frequencies
 *
 * Maps input values to a sawtooth output.
 */
class Saw implements IGenerator {
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
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ILimits::F_P2P_LEVEL_NO_CLIP * ($fValue - floor($fValue) - 0.5);
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
