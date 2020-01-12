<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Packet;


interface IFunction {

    const
        F_FULL_CYCLE = 1.0,
        F_HALF_CYCLE = 0.5
    ;

    /**
     * @param Packet $oInput
     * @return Packet
     *
     */
    public function map(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Square implements IFunction {

    public function map(Packet $oInput) : Packet {
        $aValues = [];
        foreach ($oInput->getValues() as $fValue) {

        }
    }
}

