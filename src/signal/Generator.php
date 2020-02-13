<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IGenerator;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Flat - simplest spectral complexity (zero frequencies)
 *
 * Maps to a fixed value, irrespective of input
 */
class Flat implements IGenerator {

    const F_PERIOD = 1.0;

    /**
     * @var Packet $oOutput
     */
    private $oOutput;

    /**
     * Constructor
     *
     * @param float $fLevel
     */
    public function __construct(float $fLevel = 0) {
        $this->oPacket = new Packet();
        $this->setLevel($fLevel);
    }

    /**
     * Set the level
     *
     * @param  float $fLevel
     * @return self  fluent
     */
    public function setLevel(float $fLevel) : self {
        $this->oPacket->fillWith($fLevel);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     *
     * Input packet has no effect, output is constant.
     */
    public function map(Packet $oInput) : Packet {
        return clone $this->oPacket;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for non-flat generator functions
 */
abstract class NonFlat implements IGenerator {

    protected
        $fMinLevel,
        $fMaxLevel
    ;

    /**
     * @param float $fMinLevel
     * @param float $fMaxLevel
     */
    public function __construct(
        float $fMinLevel = ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel = ILimits::F_MAX_LEVEL_NO_CLIP
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

