<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IGenerator;
use \SPLFixedArray;

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
