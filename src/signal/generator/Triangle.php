<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IGenerator;
use \SPLFixedArray;

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