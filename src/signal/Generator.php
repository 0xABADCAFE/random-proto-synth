<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Packet;

/**
 * IFunction
 *
 * Main function generator interface. Function generators generate a basic waveform, with a time-independent duty
 * cycle of 0.0 - 1.0. Values outside this range will have their integer part ignored.
 */
interface IFunction {

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
 * DC
 *
 * Outputs a fixed value, irrespective of input
 */
class DC implements IFunction {

    const F_PERIOD = 1.0;

    /**
     * @var float $fLevel
     */
    private $fLevel = 0.0;

    /**
     * Constructor
     *
     * @param float $fLevel
     */
    public function __construct(float $fLevel = 0) {
        $this->fLevel = $fLevel;
    }

    /**
     * Set the level
     *
     * @param float $fLevel
     */
    public function setLevel(float $fLevel) : DC {
        $this->fLevel = $fLevel;
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
     */
    public function map(Packet $oInput) : Packet {
        return new Packet(array_fill(0, $oInput->count(), $this->fLevel));
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Noise implements IFunction {

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
            $fNormalize = (ILimits::F_MAX_NOCLIP - ILimits::F_MIN_NOCLIP) / (float)mt_getrandmax();
        }
        $iLength = $oInput->count();
        $aValues = [];
        while ($iLength-- > 0) {
            $aValues[] = ILimits::F_MIN_NOCLIP + mt_rand() * $fNormalize;
        }
        return new Packet($aValues);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Square implements IFunction {

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
        $aValues = [];
        foreach ($oInput->getValues() as $fValue) {
            $fOutput = ($fValue & 1) ? ILimits::F_MIN_NOCLIP : ILimits::F_MAX_NOCLIP;
            $aValues[] = ($fValue < 0.0) ? -$fOutput : $fOutput;
        }
        return new Packet($aValues);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Sine implements IFunction {

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
        $aValues = [];
        foreach ($oInput->getValues() as $fValue) {
            $aValues[] = sin($fValue);
        }
        return new Packet($aValues);
    }
}
