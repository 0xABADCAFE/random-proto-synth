<?php

namespace ABadCafe\Synth\Envelope;

use \Countable;
use ABadCafe\Synth\Signal\Packet;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

interface IShape extends Countable {

    /**
     * Set the initial envelope level
     *
     * @param  float $fLevel
     * @return self  fluent
     */
    public function initial(float $fLevel) : IShape;

    /**
     * Appends a new control point to the envelope as a level/time pair. The time dictates how long, in seconds, it takes
     * to reach the new level. This value is clamped between the limits specified in ILimits
     *
     * @param  float $fLevel - the level to reach
     * @param  float $fTime  - the time, in seconds, required to reach the new level
     * @return self          - fluent
     */
    public function append(float $fLevel, float $fTime) : IShape;

    /**
     * Returns the set of control points
     *
     * @return float[][2]
     */
    public function getAll() : array;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

interface IGenerator {
    public function emit() : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'envelope/Shape.php';
require_once 'envelope/Generator.php';
