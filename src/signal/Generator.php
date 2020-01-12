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

    const
        F_FULL_CYCLE = 1.0,
        F_HALF_CYCLE = 0.5
    ;

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

class DC implements IFunction {

    private $fLevel = 0.0;

    public function setLevel(float $fLevel) : DC {
        $this->fLevel = $fLevel;
        return $this;
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

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $aValues = [];
        foreach ($oInput->getValues() as $fValue) {
            $fValue = fmod($fValue, self::F_FULL_CYCLE);
            if ($fValue < 0.0) {
                $fValue += self::F_FULL_CYCLE;
            }
            $aValues[] = $fValue >= self::F_HALF_CYCLE ? ILimits::F_MIN_NOCLIP : ILimits::F_MAX_NOCLIP;
        }
        return new Packet($aValues);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Sine implements IFunction {

    const F_SCALE = 2.0 * M_PI;

    /**
     * @inheritdoc
     */
    public function map(Packet $oInput) : Packet {
        $aValues = [];
        foreach ($oInput->getValues() as $fValue) {
            $aValues[] = sin(self::F_SCALE * $fValue);
        }
        return new Packet($aValues);
    }
}
