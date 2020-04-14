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

namespace ABadCafe\Synth\Envelope;
use \InvalidArgumentException;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Shape
 *
 * Basic envelope shape definition. Defines a list of points and levels.
 */
class Shape implements IShape {

    /** @var float[2][] $aPoints */
    private array $aPoints = [
        0 => [0, 0]
    ];

    /**
     * Constructor. Accepts an initial output level and an optional array of level/time pairs
     *
     * @param float      $fInitial
     * @param float[2][] $aPoints  - Array of level/time pairs
     *
     */
    public function __construct(float $fInitial = 0, array $aPoints = []) {
        $this->aPoints[0][0] = $fInitial;
        foreach ($aPoints as $aPoint) {
            if (!is_array($aPoint) || count($aPoint) != 2) {
                throw new InvalidArgumentException();
            }
            $this->aPoints[] = [
                (float)$aPoint[0],
                min(max((float)$aPoint[1], ILimits::F_MIN_TIME), ILimits::F_MAX_TIME)
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function count() : int {
        return count($this->aPoints);
    }

    /**
     * @inheritdoc
     */
    public function initial(float $fLevel) : IShape {
        $this->aPoints[0][0] = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function append(float $fLevel, float $fTime) : IShape {
        $this->aPoints[] = [
            $fLevel,
            min(max($fTime, ILimits::F_MIN_TIME), ILimits::F_MAX_TIME)
        ];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAll() : array {
        return $this->aPoints;
    }
}
