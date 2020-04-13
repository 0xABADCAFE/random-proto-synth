<?php

declare(strict_types = 1);

namespace ABadCafe\Synth\Signal\Generator;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Noise - all frequencies
 *
 * Maps to a randomised value, irrespective of input
 */
class Noise extends NonFlat {

    const F_PERIOD = 1.0;

    protected float
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
