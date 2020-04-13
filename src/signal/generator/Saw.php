<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal;
use \SPLFixedArray;

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
    public function map(Signal\Packet $oInput) : Signal\Packet {
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
 * SawDown - series of frequencies
 *
 * Maps input values to a downwards sawtooth output.
 */
class SawDown extends SawUp {

    /**
     * @inheritdoc
     */
    public function map(Signal\Packet $oInput) : Signal\Packet {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleLevel * (ceil($fValue) - $fValue) + $this->fMinLevel;
        }
        return $oOutput;
    }

}

