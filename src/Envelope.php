<?php

/**
 *      _/_/_/  _/_/_/  _/      _/  _/_/_/      _/_/_/
 *   _/          _/    _/_/  _/_/  _/    _/  _/
 *  _/  _/_/    _/    _/  _/  _/  _/_/_/      _/_/
 * _/    _/    _/    _/      _/  _/              _/
 *  _/_/_/  _/_/_/  _/      _/  _/        _/_/_/
 *
 *  - Grossly Impractical Modular PHP Synthesiser -
 *
 */

declare(strict_types = 1);

/**
 * Envelope
 */
namespace ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Map;
use \Countable;

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

/**
 * IGenerator
 *
 * Basic tag interface for Envelope Generators
 */
interface IGenerator extends Signal\Control\IStream, Map\Note\IMIDINumberAware {

    const
        // Use cases for Map\Note\IMIDINumberAware
        S_NOTE_MAP_SPEED = 'speed',
        S_NOTE_MAP_LEVEL = 'level'
    ;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IShape
 *
 * Basic interface for entities that define an Envelope Shape
 */
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

/**
 * IShaped
 *
 * Basic tag interface generators that use shape sets
 */
interface IShaped {

    /**
     * Set the envelope shape. Will trigger a reset.
     *
     * @param  IShape $oShape
     * @return self   fluent
     */
    public function setShape(IShape $oShape) : self;

    /**
     * Get the envelope shape.
     *
     * @return IShape
     */
    public function getShape() : IShape;
}
