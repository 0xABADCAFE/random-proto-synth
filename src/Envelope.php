<?php

namespace ABadCafe\Synth\Envelope;

use \Countable;

/**
 * ILimits
 *
 * Defines limits for envelope data.
 */
interface ILimits {
    const
        F_MIN_TIME = 0.0001,
        F_MAX_TIME = 100.0
    ;
}

/**
 * Basic shape definition. Defines a list of points and levels.
 */
class Shape implements Countable {

    private
        $aPoints = []
    ;

    public function count() : int {
        return count($this->aPoints);
    }

    public function add(float $fLevel, float $fTime) : self {
        $this->aPoints[] = [
            $fLevel,
            min(max($fTime, ILimits::F_MIN_TIME), ILimits::F_MAX_TIME)
        ];
    }
}
