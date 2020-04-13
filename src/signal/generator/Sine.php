<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal;
use \SPLFixedArray;

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
    public function map(Signal\Packet $oInput) : Signal\Packet {
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
