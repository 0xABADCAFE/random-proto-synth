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

namespace ABadCafe\Synth\Signal\Generator;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for non-flat generator functions
 */
abstract class Base implements Signal\IGenerator {

    protected float
        $fMinLevel,
        $fMaxLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = Signal\ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = Signal\ILimits::F_MAX_LEVEL_NO_CLIP
    ) {
        $this->fMinLevel = min($fMinLevel, $fMaxLevel);
        $this->fMaxLevel = max($fMinLevel, $fMaxLevel);
        $this->init();
    }

    /**
     * Perform any initialisation after setting the levels
     */
    protected function init() {

    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'generator/Sine.php';
require_once 'generator/Square.php';
require_once 'generator/Saw.php';
require_once 'generator/Triangle.php';
require_once 'generator/Noise.php';
require_once 'generator/WaveTable.php';
require_once 'generator/Factory.php';
