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
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Basic triangle generator
 */
class Triangle extends Primitive {

    const F_PERIOD = 2.0;

    protected float
        $fBiasLevel,
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Signal\IPacket $oInput) : Signal\IPacket {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        if ($this->oShaper) {
            foreach ($oValues as $i => $fValue) {
                $fValue      = $this->oShaper->modifyInput($fValue);
                $fValue     -= 0.5;
                $fFloor      = floor($fValue);
                $fScale      = (int)$fFloor & 1 ? $this->fScaleLevel : -$this->fScaleLevel;
                $oValues[$i] = $this->oShaper->modifyOutput($this->fBiasLevel + $fScale*($fValue - $fFloor - 0.5));
            }
        } else {
            foreach ($oValues as $i => $fValue) {
                $fValue     -= 0.5;
                $fFloor      = floor($fValue);
                $fScale      = (int)$fFloor & 1 ? $this->fScaleLevel : -$this->fScaleLevel;
                $oValues[$i] = $this->fBiasLevel + $fScale*($fValue - $fFloor - 0.5);
            }
        }
        return $oOutput;
    }

    /**
     * @overriden
     */
    protected function init() {
        $this->fBiasLevel  = 0.5*($this->fMaxLevel + $this->fMinLevel);
        $this->fScaleLevel = $this->fMaxLevel - $this->fMinLevel;
    }
}
