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

namespace ABadCafe\Synth\Signal\Waveform;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for non-flat generator functions
 */
abstract class Primitive implements Signal\IWaveform {

    protected float
        $fMinLevel,
        $fMaxLevel
    ;

    protected ?IShaper $oShaper = null;

    /**
     * @param float        $fMinLevel
     * @param float        $fMaxLevel
     * @param IShaper|null $oShaper
     */
    public function __construct(
        float $fMinLevel      = Signal\ILimits::F_MIN_LEVEL_NO_CLIP,
        float $fMaxLevel      = Signal\ILimits::F_MAX_LEVEL_NO_CLIP,
        ?IShaper $oShaper = null
    ) {
        $this->fMinLevel = min($fMinLevel, $fMaxLevel);
        $this->fMaxLevel = max($fMinLevel, $fMaxLevel);
        $this->oShaper   = $oShaper;
        $this->init();
    }

    /**
     * @param  IShaper|null
     * @return self
     */
    public function setShaper(?IShaper $oShaper) : self {
        $this->oShaper = $oShaper;
        return $this;
    }

    /**
     * Perform any initialisation after setting the levels
     */
    protected function init() {

    }
}
