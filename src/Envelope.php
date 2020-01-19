<?php

namespace ABadCafe\Synth\Envelope;

use \Countable;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;

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

    private $aPoints = [
        0 => [0, 0]
    ];

    /**
     * @inheritdoc
     */
    public function count() : int {
        return count($this->aPoints);
    }

    public function initial(float $fLevel) : self {
        $this->aPoints[0][0] = $fLevel;
        return $this;
    }

    /**
     * Appends a new control point to the envelope as a level/time pair. The time dictates how long, in seconds, it takes
     * to reach the new level. This value is clamped between the limits specified in ILimits
     *
     * @param float $fLevel - the level to reach
     * @param float $fTime  - the time, in seconds, required to reach the new level
     */
    public function append(float $fLevel, float $fTime) : self {
        $this->aPoints[] = [
            $fLevel,
            min(max($fTime, ILimits::F_MIN_TIME), ILimits::F_MAX_TIME)
        ];

        return $this;
    }

    /**
     * Returns the set of control points
     *
     * @return float[][]
     */
    public function getAll() : array {
        return $this->aPoints;
    }
}

require_once 'envelope/Generator.php';
